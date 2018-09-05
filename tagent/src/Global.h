#pragma once
#include "Base.h"
#include <string>
#include <vector>
#include <map>
#include <cstdio>
#include <cassert>
#include <sys/io.h>
#include <dirent.h>
#include <unistd.h>
#include "json/json.h"
#include "ConfExec.h"
#include "Log.h"
#include "../include/librdkafka/rdkafka.h"
typedef long long int LINT;

// 线程参数
struct threadArg {
    threadArg(std::string logPath, std::string style, std::string format = "")
    :_logPath(logPath)
    ,_style(style)
    ,_format(format)
    {}
    std::string _logPath;
    std::string _style;
    std::string _format;
};

std::map<std::string, LogPair>   g_confMap;     // 路径 -> 监听规则
std::map<std::string, pthread_t> g_confThread;  // 路径 -> 线程pid
std::vector<std::string>         g_confVec;     // 配置文件名称集合 (nature)
BusinessMap			             g_businessMap; // 业务监控项汇总
ProcPair                         g_procConf;    // 配置(process)
PortPair                         g_portConf;    // 配置(port)


std::string DATA_PATH = "./data/Agent.data";  // 数据存放路径
int CPU_SAM_INTERVAL = 7000;                  // CPU信息采样间隔(毫秒)
int SYSINFO_INTERVAL = 0;                     // 系统信息间隔(秒)
LINT g_time = getNowTime();                   // 时间 (h)

rd_kafka_t* g_rk          = NULL;             // rdkafka 实例
rd_kafka_topic_t* g_rkt   = NULL;              // rdkafka topic
rd_kafka_conf_t* g_rkconf = NULL;             // rdkafka 配置

std::string g_ROOT_PATH = "";                 // Agent根路径

std::string g_hostName;
std::string g_localIp;


LINT g_updateTime = 0;


/* d
 * bug */
void print_g_confMap() {
    std::cout << "g_confMap : " << std::endl;
    std::cout << "size: " << g_confMap.size() << std::endl;
    for(std::map<std::string, LogPair>::iterator it = g_confMap.begin(); it != g_confMap.end(); ++it) {
        printf("__________________\n");
        LogPair logPair = it->second;
        std::cout << logPair._path   << std::endl;
        std::cout << logPair._style  << std::endl;
        std::cout << logPair._format << std::endl;
        // 匹配规则
        for(std::map<std::string, RulePair>::iterator it1 = logPair._ruleMap.begin(); it1 != logPair._ruleMap.end(); ++it1) {
            std::cout << "_filt: " << it1->second._filt << std::endl;
            if(it1->second._isAwk) {
                printf("Awk");
            }else if(it1->second._isSearch) {
                printf("Search");
            }
            std::cout << "_match: " << it1->second._match << std::endl;
            std::cout << "_search: " << it1->second._isSearch << std::endl;
            std::cout << "names : ";
            printVector(it1->second._nameVec);
            std::cout << std::endl;
        }
        // 集群映射
        printf("group  ->  ruleNames:\n");
        for(std::map<std::string, std::vector<std::string> >::iterator it1 = logPair._groupMap.begin(); it1 != logPair._groupMap.end(); ++it1) {
            std::cout << it1->first << " -> ";
            printVector(it1->second);
        }
        printf("\n__________________\n\n\n");
    }
}
/*
*   读取配置文件中的内容，更新到内存中
*   @param  confName, 也就是集群名称
*   @return vector<string> path 
*/
std::vector<threadArg> getConf(const char* confName) {
    // 读取配置文件
    std::string path = g_ROOT_PATH + "item_conf/" + std::string(confName);
    std::ifstream ifs;

    // std::cout << path << std::endl;
    
    ifs.open(path.c_str());
    if(!ifs) {
        std::cout << "conf error at Global.h, return!" << std::endl;
        exit(0);
    }

    // 存放的方式不变，只改变读取的方式
    Json::Reader reader;
    Json::Value  root;

    reader.parse(ifs, root, false);
    ifs.close();
    Json::Value logVal  = root["log"];
    Json::Value procVal = root["proc"];
    Json::Value portVal = root["port"];
    /* 日志相关配置 */
    std::vector<threadArg> argVec;   // 要创建的线程参数 (返回值)
    for(int i = 0; i < logVal.size(); ++i) {   // 读取当前集群要监听的所有日志及相应规则
        // 读取内容
        std::string logPath     = (logVal[i]["property"]["log_path"]).asString();
        std::string logStyle    = (logVal[i]["property"]["log_style"]).asString();
        std::string logFormat   = (logVal[i]["property"]["time_format"]).asString();
        // 如果没有当前路径的监听线程，则作为返回值，返回给上游进行调用
        std::map<std::string, pthread_t>::iterator it = g_confThread.find(logPath);
	    std::cout << "checking " << logPath << std::endl;
        if(g_confThread.end() == it) {
	        std::cout << "not existed, create!" << std::endl;
            threadArg arg(logPath, logStyle, logFormat); 
            argVec.push_back(arg);
            std::cout << "-- [p] [u] [s] [h] --";
        }
	    std::cout << "existed" << std::endl;
        LogPair& logPair = g_confMap[logPath];
        // 设置基本信息
        logPair.setConf(logPath, logStyle, logFormat);
        // 设置监听规则 j
        for(int j = 0; j < logVal[i]["conf"].size(); ++j) {
            std::string filt    = logVal[i]["conf"][j]["filt_str"].asString();
            std::string awk     = logVal[i]["conf"][j]["awk_str"].asString();
            std::string match   = logVal[i]["conf"][j]["match_str"].asString();  // 正则匹配
            std::string search  = logVal[i]["conf"][j]["search_str"].asString(); // 正则搜索
            std::string name    = logVal[i]["conf"][j]["item_name_prefix"].asString();
            // 根据配置项中 awk_str的有无决定存入正则还是awk
            if(!awk.empty()) {
                logPair.addRule(std::string(confName), filt, awk, name, true, false);   
            }else if(!search.empty()) {
                printf("[SEARCH] filt=%s, str=%s, name=%s", filt.c_str(), search.c_str(), name.c_str());
                logPair.addRule(std::string(confName), filt, search, name, false, true);                  
            }else {
                printf("[MATCH] filt=%s, str=%s, name=%s", filt.c_str(), match.c_str(), name.c_str());
                logPair.addRule(std::string(confName), filt, match, name, false, false);                  
            }
        }
        // 添加到 g_confMap
        g_confMap[std::string(logPath)] = logPair;
    }
    /* 进程相关配置 */
    std::cout << "xxxxxx p r o c xxxxxxxx" << std::endl;
    for(int i = 0; i < procVal.size(); ++i) {
        // 读取内容
        std::string procPath    = (procVal[i]["proc_path"]).asString();
        std::string name        = (procVal[i]["item_name_prefix"]).asString();
	    std::string deeplyPath  = (procVal[i]["proc_deeply_path"]).asString();
        
        std::cout << procPath << std::endl;
        std::cout << name << std::endl;
	    std::cout << deeplyPath << std::endl;

        // 加载
        g_procConf.addRule(std::string(confName), name, procPath, deeplyPath);
    }
    
    /* 端口相关配置 */
    std::cout << "xxxxxx p o r t xxxxxxxx" << std::endl;
    
    for(int i = 0; i < portVal.size(); ++i) {
        // 读取内容
        std::string portNum = (portVal[i]["port_num"]).asString();
        std::string name    = (portVal[i]["item_name_prefix"]).asString();
        
        std::cout << portNum << std::endl;
        std::cout << name << std::endl;

        // 加载
        g_portConf.addRule(std::string(confName), name, portNum);
    }
    std::cout << "add conf compelete" << std::endl;
    return argVec;
}

/*
*   删除配置  
*   @param  配置路径(也就是集群名)
*/
void deleteConf(const char* confName) {
    // 遍历 g_confMap, 找到包含当前集群项
    std::cout << "delete conf:" << confName << std::endl;
    std::vector<std::string> removePath;    
    // (导致死循环了)
    for(std::map<std::string, LogPair>::iterator it = g_confMap.begin(); it != g_confMap.end(); ++it) {
        std::cout <<  "zzzzz    zzzzzzz " << std::endl; 
        LogPair& logPair = it->second;
        std::cout << g_confMap.size() << std::endl;
        logPair.isGroupExisted(std::string(confName));
        if(logPair.isGroupExisted(std::string(confName))) {  // 当前logPair中存在来自当前集群的配置
            std::cout << "existed" << std::endl;
            std::vector<std::string> pathVec = logPair.removeGroup(std::string(confName));
            if(!pathVec.empty()) {  // 删除后若logPair为空需要清空配置项
                std::cout << "is empty!" << std::endl;
                for(std::vector<std::string>::iterator it = pathVec.begin(); it != pathVec.end(); ++it) {
                    std::string path = *it;
                    g_confMap.erase(path);
                    std::cout << "erase over" << std::endl;
                    int ret = pthread_cancel(g_confThread[path]); // 杀进程
	        	    std::cout << "杀进程的结果： " << ret << std::endl;
                }
            }
        }
    }
    // 处理之前存放的，要清除的配置 这只是暂时的方法  一定有更好的办法...
    for(std::vector<std::string>::iterator it = removePath.begin(); it != removePath.end(); ++it) {
        g_confMap.erase(*it);
    }
    // 清除进程、端口号的相关配置
    g_procConf.removeGroup(std::string(confName));
    g_portConf.removeGroup(std::string(confName));
}

/* 获取路径下的所有.conf文件，并返回 */
std::vector<std::string> getFiles(std::string path){  
    char ch;
    char infile[50];
    char outfile[50];
    struct dirent* ptr;
    std::vector<std::string> retVec;
    DIR* dir;
    dir = opendir("./item_conf");
    while( (ptr = readdir(dir)) != NULL ) {
        // 跳过 "." 和 ".."
        if(ptr->d_name[0] == '.') {
            continue;
        }
        std::string confName = std::string(ptr->d_name);
        if(confName.find(".conf") != std::string::npos){
            retVec.push_back(confName);
        }
    }
    return retVec;
}

/*
*   初始化全局变量
*/
void InitGlobal() {
    g_hostName = getHostName();   
    g_localIp  = getLocalIp();
}

/* 初始化配置文件 */
void InitConf() {
    // 设置Agent根目录
    char dir[PATH_MAX] = {0};  
    readlink("/proc/self/exe", dir, PATH_MAX);  
    std::string path = std::string(dir);
    int pos = path.find_last_of('/');
    path    = path.substr(0, pos);
    pos     = path.find_last_of('/');
    path    = path.substr(0, pos + 1);
    g_ROOT_PATH = path;

    std::cout << g_ROOT_PATH << std::endl;
    
    // 加载配置文件
    g_confVec = getFiles(g_ROOT_PATH + "item_conf/");  // 获取全部配置文件的名称

    // 将获取到的配置文件加载到内存中
    for(size_t i = 0; i < g_confVec.size(); ++i) {
        getConf(g_confVec[i].c_str());
    }

    // 初始化kafka连接相关
    char errstr[512];
    const char* brokers  = "xxx.xxx.xxx.xxx:port,xxx.xxx.xxx.xxx:port,xxx.xxx.xxx.xxx:port"; // 修改此处
    const char* api_version_request = "false";
    const char* api_version_fallback = "0.8.2.0";
    
    std::string topicStr = readFileIntoString("/usr/local/tagent/data/topic");
    Log log = Log("nature");
    log.append("Init kafka connection ...");
    if (topicStr.empty()) {
	log.appendFatal("Unknown topic name in file : /usr/local/tagent/data/topic ...", 1);
	exit(0);
    }
    topicStr.erase(0, topicStr.find_first_not_of("\n"));
    topicStr.erase(topicStr.find_last_not_of("\n") + 1);
    std::cout << "topic name is : " << topicStr << std::endl;
    g_rkconf = rd_kafka_conf_new();
    if (rd_kafka_conf_set(g_rkconf, "bootstrap.servers", brokers, errstr, sizeof(errstr)) != RD_KAFKA_CONF_OK) {
        fprintf(stderr, "%s\n", errstr);
        exit(0);
    }
    if (rd_kafka_conf_set(g_rkconf, "api.version.request", api_version_request, errstr, sizeof(errstr)) != RD_KAFKA_CONF_OK) {
        fprintf(stderr, "%s\n", errstr);
        exit(0);
    }
    if (rd_kafka_conf_set(g_rkconf, "broker.version.fallback", api_version_fallback, errstr, sizeof(errstr)) != RD_KAFKA_CONF_OK) {
        fprintf(stderr, "%s\n", errstr);
        exit(0);
    }
    g_rk = rd_kafka_new(RD_KAFKA_PRODUCER, g_rkconf, errstr, sizeof(errstr));
    if (!g_rk) {
        fprintf(stderr, "%% Failed to create new producer: %s\n", errstr);
        exit(0);
    }
    g_rkt = rd_kafka_topic_new(g_rk, topicStr.c_str(), NULL);
    if (!g_rkt) {
            fprintf(stderr, "%% Failed to create topic object: %s\n", rd_kafka_err2str(rd_kafka_last_error()));
            rd_kafka_destroy(g_rk);
            exit(0);
    }
    rd_kafka_poll(g_rk, 0/*non-blocking */);

}                

std::string getLogPath(std::string name) {
    return g_confMap[name]._path;
}

std::string getFormat(std::string name) {
    return g_confMap[name]._format;
}
