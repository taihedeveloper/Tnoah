/*
*   业务监控
*/
#pragma once


#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/inotify.h>
#include <string>
#include <vector>

#include <sys/types.h>
#include <fcntl.h>
#include "Global.h"
#include "CMsgQueue.h"



// 匹配单元 (正则和awk都用这个结构体作为类型进行传参)
struct MatchUnit {
    MatchUnit(const std::string& filt, const std::string& match, const std::vector<std::string>& nameVec) 
    :_filt(filt)
    ,_match(match)
    ,_nameVec(nameVec)
    {}
    std::string _filt;
    std::string _match;
    std::vector<std::string> _nameVec;

    // DEBUG
    void printUnit() {
        std::cout << "match: " << _match << ", filt: " << _filt << "names: ";
        printVector(_nameVec);
    }
};

// inotify用到的结构体
struct st_files{
    char *filename;
    int wd;
};
struct st_files file_wd;

CMsgQueue* g_msgQue = CMsgQueue::getInstance();    // 消息队列-最终输出
CMsgQueue* g_logQue = CMsgQueue::getInstance();    // 缓冲日志信息 (最后应该没用到)

/**
*   获取Newly风格日志的文件名
*   @param o_path 初始文件名
*   @param format 格式
*   @param 偏移量
*/
std::string getNewlyLogName(std::string o_path, std::string format, LINT offset = 0) {
    // 获取时间
    time_t timestamp = getTimeStamp() + offset;
    char buf[128];
    struct tm* timeinfo = localtime(&timestamp);
    strftime(buf, sizeof(buf), format.c_str(), timeinfo);
    std::string strTime = std::string(buf);
    // 获取文件名
    size_t pos = o_path.find("{}");
    if(pos >= o_path.size()) {
        std::cout << "[FATAL] not fount .{} in your conf, o_path=" << o_path << std::endl;
        return o_path;
    }
    o_path.replace(pos, 2, strTime);
    return o_path;
}

/* 
*   对传入的内容进行正则匹配 
*   @param1 内容
*   @param2 规则
*   @return  格式： rule1@1 rule2@true ...
*/
std::string regexMatch(const std::string& content, const std::vector<MatchUnit>& rules) {
    std::string retStr = "";
    int maxI = rules.size(); 

    for(int i = 0; i < maxI; ++i) {
        const std::string& filt  = rules[i]._filt;
        const std::string& match = rules[i]._match;
        const std::vector<std::string>& names = rules[i]._nameVec;
        // 匹配过滤字符串
        if(!filt.empty()) {
            if( doRegex(content, filt) ) {   // 过滤
    	    	std::cout << "[NOTICE] line was filted" << std::endl;
                continue;
            }
        }
        if(!match.empty()) {
            if( doRegex(content, match) ) { // 匹配到
                int idxMax = names.size();
                for(int idx = 0; idx < idxMax; ++idx) {
                    const std::string& name = names[idx];
                    if(g_businessMap.check()) {  // merge per 10 seconds
		                retStr += g_businessMap.getOutput(); 
			            g_businessMap.init(g_confMap);
		            }   
                    g_businessMap.increaseSum(name);
                }
            }
        }
    }
    return retStr;
}


/*
*   对传入的内容进行正则查找
*/
std::string searchMatch(const std::string& content, const std::vector<MatchUnit>& rules) {
    std::string retStr = "";

    int maxI = rules.size();
    for(int i = 0; i < maxI; ++i) {
        const std::string& filt  = rules[i]._filt;
        const std::string& match = rules[i]._match;        
        const std::vector<std::string>& names = rules[i]._nameVec;
        // 匹配过滤字符串
        if(!filt.empty()) {
            if(doRegex(content, filt)) {
    	    	std::cout << "[NOTICE] line was filted" << std::endl;
                continue;
            }
        }
        if(!match.empty()) {
            const std::string searchRet = doRegexSearchBoost(content, match);
            // 为了性能, 这里不会验证匹配结果是否为数字
            if(!searchRet.empty()){
                int idxMax = names.size();
                for(int idx = 0; idx < idxMax; ++idx) {
                    const std::string& name = names[idx];
                    if(g_businessMap.check()) {
                        retStr += g_businessMap.getOutput();
		                g_businessMap.init(g_confMap);
                    }
                    g_businessMap.increaseAve(name, atof(searchRet.c_str()));
                }
            }
        }
    }
    return retStr;
}

/*
*   对传入的内容进行按列匹配
*/
std::string awkMatch(const std::string& content, const std::vector<MatchUnit>& rules) {
    std::string retStr = "";    
    int maxI = rules.size();
    for(int i = 0; i < maxI; ++i) {
        const std::string& filt  = rules[i]._filt;
        const std::string& match = rules[i]._match;
        const std::vector<std::string>& names = rules[i]._nameVec;

        int pos = atoi( (match.substr(1)).c_str());
        // 匹配过滤字符串
        if(!filt.empty()) {
            if( doRegex(content, filt) ) {   // 过滤
	        	std::cout << "line was filted" << std::endl;
                continue;
            }
        }
        if(!match.empty()) {
            std::string getStr = doAwk(content, pos);
	        if(getStr.empty()) {
		        return "";
            }
            int idxMax = names.size();
            for(int idx = 0; idx < idxMax; ++idx) {
                const std::string& name = names[idx];
                if(g_businessMap.check()) {
		            retStr += g_businessMap.getOutput();
		            // std::cout << retStr << std::endl;
		            g_businessMap.init(g_confMap);
            	}
                g_businessMap.increaseAve(name, atof(getStr.c_str()));
            }
        }
    }
    return retStr;
}

/* 
 * 根据配置项，对读取到的内容进行匹配 
 * @param1  fp
 * @param2  logPair
 * @param3  needUpdate  (对于曲库等集群日志分割采用cp && echo "" > 的方式, 需要通过一个字符串校验)
*/
std::string matchExec(FILE* fp, LogPair& logPair, bool& needUpdate) {
    char buf[4096];
    std::string matchStr = "";
    while( NULL != fgets(buf, sizeof(buf), fp) ) {    // 按行读，这样实现不需要额外队列
        std::string line = std::string(buf);
        if(!line.empty()) {
            //  对于特殊切分规则下日志的处理
            if(strcmp(line.c_str(), "[tagent: this log file need to be reload]\n") == 0 || strcmp(line.c_str(), "[tagent: this log file need to be reload]") == 0) {
                needUpdate = true;            
                return "";
            }
            std::map<std::string, RulePair>& ruleMap = logPair._ruleMap;
            std::vector<MatchUnit> regexVec;    // 存放待匹配的正则匹配规则
            std::vector<MatchUnit> searchVec;   // 存放待匹配的正则查找规则
            std::vector<MatchUnit> awkVec;      // 存放待匹配的awk规则
            // 将当前文件的所有匹配项汇集到一起进行相应的匹配
            for(std::map<std::string, RulePair>::iterator it = ruleMap.begin(); it != ruleMap.end(); ++it) {
                RulePair& rulePair = it->second;
                const std::string& filt  = rulePair._filt;
                const std::string& match = rulePair._match;
                bool isAwk = rulePair._isAwk;
                bool isSearch = rulePair._isSearch;
                const std::vector<std::string>& nameVec = rulePair._nameVec;

                MatchUnit unit = MatchUnit(filt, match, nameVec);

                if(isAwk) {
                    awkVec.push_back(unit);
                }else if(isSearch) {
                    searchVec.push_back(unit);                                
                }else {
                    regexVec.push_back(unit);                                
                }
            }
            matchStr += regexMatch(line, regexVec);  // 正则匹配
            matchStr += searchMatch(line, searchVec);  // 正则查找
            matchStr += awkMatch(line, awkVec);  // Awk匹配
        }    
    }
   return matchStr;
}

/* Newly风格日志
*  这部分写得不好，应该能优化
*/
void readingNewlyStyleLogs(const char* file, bool needHour = true, bool needMin = false) {
    // 获取更新的时间偏移量
    LINT offset = 3600;
    if(!needHour && !needMin) {
        offset = 3600 * 24;
    } else if(needHour && !needMin) {
        offset = 3600;
    } else {
        offset = 60;
    }
    // 获取路径
    std::string key     = std::string(file);
    LINT curTime        = getNowTime(needHour, needMin);

    std::string filePathModel = getLogPath(key);    // 文件名模板
    std::string format        = getFormat(key);
    std::string filePath      = getNewlyLogName(filePathModel, format, 0);
    std::cout << filePath << std::endl;
    FILE* fp = fopen(filePath.c_str(), "r");
    while(fp == NULL) {
        perror("open file failed");
        std::cout << filePath << std::endl;
        sleep(5);
        fp = fopen(filePath.c_str(), "r");        
    }
    int fd = inotify_init();
    // 将 fd 设置为非阻塞
    int flags = fcntl(fd, F_GETFL, 0);
    flags |= O_NONBLOCK;
    fcntl(fd, F_SETFL, flags);
    // 初始化监听
    int wd = inotify_add_watch(fd, filePath.c_str(), IN_MODIFY);
    char buffer[1024];
    while(wd < 0) {
        perror("inotify_add_watch failed");
        sleep(5);
        wd = inotify_add_watch(fd, filePath.c_str(), IN_MOVE_SELF | IN_MODIFY);
    }
    int ch = 0;
    while( (ch = fgetc(fp)) != EOF );   // 将文件指针定位到日志文件的末尾

    LINT nextUpdateTime = getNextUpdateTime(offset, needHour, needMin); // 下一次更新的时间

    while(true) {
        curTime = getNowTime(needHour, needMin);
        // 更换读取的文件
        if(curTime >= nextUpdateTime) {
            inotify_rm_watch(fd, wd);
            fclose(fp);
            close(fd);
            sleep(3);
            std::string filePath = getNewlyLogName(filePathModel, format, 0);
            std::cout << "time off, open new log file .." << filePath << std::endl;  

            fp = fopen(filePath.c_str(), "r");
            while(fp == NULL){
                perror("open file failed");
                std::cout << filePath << std::endl;
                sleep(5);
                fp = fopen(filePath.c_str(), "r");
            }
            fd = inotify_init();
            // 将fd 设置为非阻塞
            flags = fcntl(fd, F_GETFL, 0);
            flags |= O_NONBLOCK;
            fcntl(fd, F_SETFL, flags);
            wd = inotify_add_watch(fd, filePath.c_str(), IN_MODIFY);
            nextUpdateTime = getNextUpdateTime(offset, needHour, needMin);
        }

        if( read(fd, buffer, 1024) < 0 ) { // 文件没有更新, 则一直等文件更新为止
            usleep(500000); // 0.5s
            continue;
	    }

        LogPair logPair = g_confMap[key];

        bool useless = false;
        std::string matchStr = matchExec(fp, logPair, useless);
	    if(matchStr.size() > 0) {
	        g_msgQue->push(matchStr);
	    }
    }
}

/* tail -f 日志生成 & 分割格式如 access.log */
void readingSplitStyleLogs(const char* file) {
    std::string key = std::string(file);

    file = getLogPath(key).c_str();
    FILE* fp = fopen(file, "r");
    
    std::cout << file << std::endl;
    while(fp == NULL) {
        perror("open file failed");
        std::cout << file << std::endl;
        sleep(5);
        fp = fopen(file, "r");
    }
    int fd = inotify_init(); // 初始化inotify程序;
    int wd = inotify_add_watch(fd, file, IN_MOVE_SELF | IN_MODIFY); // 添加需要监测的文件与相应的行为;
    char buffer[1024];
    while(wd < 0) {
        perror("inotify_add_watch failed");
        sleep(5);
        wd = inotify_add_watch(fd, file, IN_MOVE_SELF | IN_MODIFY);
    }
    file_wd.filename = (char*)file;
    file_wd.wd = wd;
    int ch = 0;
    while( (ch = fgetc(fp)) != EOF );   // 将文件指针定位到日志文件的末尾

    while(int len = read(fd,buffer,1024)){//读取相关事件,read为阻塞
        // 读取配置
        LogPair& logPair = g_confMap[key];
        char* offset = buffer;
        struct inotify_event* event = (struct inotify_event *)buffer;
        // 监听文件变化
        while((char *)event - buffer <len) {
            int tmp_len = 0;
            if(event->wd!=file_wd.wd) {
                continue;
            }

            if(event->mask & IN_MODIFY) {   // 文件更新
               bool needUpdate = false;
               std::string matchStr = matchExec(fp, logPair, needUpdate); 
               // if(needUpdate) {
               //      std::cout << "needUpdate:true" << std::endl;
               // }else {
               //      std::cout << "needUpdate:false" << std::endl;
               // }
               if(needUpdate) {
                   char restartBuf[4096];
                   std::cout << "Move" << std::endl;  // 重新启动初始化 (echo "" > 这种特殊情况)
                   sleep(30);
                   const std::string cmd = "cd /usr/local/tagent && ./load_agent.sh restart";
                   executeCMD(cmd.c_str(), restartBuf, sizeof(restartBuf));
                   sleep(1);
                   exit(0);
                   continue;
               }
	           if(matchStr.size() > 0) {
                   g_msgQue->push(matchStr);
               }
            } else if(event->mask & IN_MOVE_SELF) {  // 整点分割
                std::cout << "Move" << std::endl;
                // 重新打开路径下的文件
                inotify_rm_watch(fd, wd);
                fclose(fp);
                close(fd);
                file = getLogPath(key).c_str();
                fp = fopen(file, "r");
                while(fp == NULL) {
                    fp = fopen(file, "r");                
                    std::cout << "cannot open " << std::endl;
		            std::cout << file << std::endl;
                    sleep(1);
                }
                fd = inotify_init();
                wd = inotify_add_watch(fd, file, IN_MOVE_SELF | IN_MODIFY);

                tmp_len=sizeof(struct inotify_event)+event->len;
                event=(struct inotify_event*)(offset+tmp_len);
                offset+=tmp_len;
                continue;
            }else{
                std::cout << "[FATAL] unknown ope ... while reading log file .." << std::endl; 
            }
            tmp_len=sizeof(struct inotify_event)+event->len;
            event=(struct inotify_event*)(offset+tmp_len);
            offset+=tmp_len;
        }
    }
}
