#pragma once
#include <string>
#include <vector>
#include <pthread.h>

#include "Global.h"
#include "Base.h"
#include "ILock.h"
#include "json/json.h"

/*
    匹配规则 
*/
struct RulePair {

    /*
    *   match: match_str 或 awk_str 的内容 (api接口保证这两项不能共存)
    *   isAwk: 按列匹配
    *   isSearch: 正则查找
    */
    RulePair(const std::string& filt="", const std::string& match="", const std::string& name="", bool isAwk = false, bool isSearch = false)
    :_filt(filt)
    ,_match(match)
    ,_isAwk(isAwk) 
    ,_isSearch(isSearch) {
        _nameVec.push_back(name);
    }

    std::string _filt;
    std::string _match;
    std::vector<std::string> _nameVec;
    bool _isAwk;    // 这个标志位用来表示匹配规则为 按列匹配
    bool _isSearch; // 这个标志位用来判断匹配规则为 正则查找

    void addName(const std::string& name) {
        _nameVec.push_back(name);
    }

    /*  找到  :  index   没找到: -1 */
    int findName(std::string name) {
        for(int i = 0; i < _nameVec.size(); ++i) {
            if(name == _nameVec[i]) {
                return i;
            }
        }
        return -1;
    }

    // debug
    void printNames() {
        for(size_t i = 0; i < _nameVec.size(); ++i) {
            std::cout << _nameVec[i] << ", ";
        }
        std::cout << std::endl;
    }

    /* 根据传入的下标移除名字 */
    void eraseName(int idx) {
        _nameVec.erase(_nameVec.begin() + idx);      
    }

    /* 判断还有没有匹配规则 */
    bool isEmpty() {
        return _nameVec.empty();
    }
};

/* 针对对指定path的日志的监控规则  该类每个实例都指定唯一的路径*/
struct LogPair {
    std::string _path;
    std::string _style;
    std::string _format;   // 精确度 ('day', 'hour', 'minute' , 只有newly风格日志才需要)

    std::map<std::string, RulePair> _ruleMap;      // 匹配项(match_str 或 search_str  或 awk_str的内容) -> 整个匹配规则的映射
    std::map<std::string, std::vector<std::string> > _groupMap;  // 集群   -> 匹配项名称集合
    /* 例如 集群A:   aaa   bbb    ccc   
     *      集群B:   bbb   ddd    fff
    */
    LogPair(std::string path = "", std::string style = "split", std::string format = "none")
    :_path(path)
    ,_style(style)
    ,_format(format)
    {}

    void setConf(std::string path, std::string style, std::string format) {
        _path     = path;
        _style    = style;
        _format   = format;
    }

    /* 判断当前规则下是否有来自于某集群的配置 */
    bool isGroupExisted(const std::string& groupName) {
        return (_groupMap.find(groupName) != _groupMap.end());
    }


    /*
    *   增加监控规则
    *   @param  groupName: 集群名称
    */
    void addRule(std::string groupName, std::string filt, std::string match, std::string name, bool isAwk, bool isSearch) {
	    std::cout << "a d d r u l e ! " << std::endl;
        std::string awkStr    = isAwk? "1" : "0";
        std::string searchStr = isSearch? "1" : "0";
        std::string key = match + "&" + filt + "&" + awkStr + "&" + searchStr;
        if(_ruleMap.find(key) != _ruleMap.end()) {  // 匹配规则已经存在
            RulePair& curRule = _ruleMap[key];
            curRule.addName(name);
            return;
        }
        // 过滤规则不同或匹配规则不同，都需要新创建监控规则
        RulePair newRule = RulePair(filt, match, name, isAwk, isSearch);
        _ruleMap[key]  = newRule;
        // 将监控项名称存入 groumMap对应的集群下
	    std::cout << "sz_before:" <<  _groupMap.size() << std::endl;
        _groupMap[groupName].push_back(name);
	    std::cout << "sz_now:" <<  _groupMap.size() << std::endl;
    }

    /* 获取监控项数目 */
    int getCount() {
        return _ruleMap.size();
    }

    /* 
        删除监控项(以集群为单位)(弃用)
        @return  需要删除的线程pid vector
     */
    std::vector<std::string> removeGroup(std::string groupName) {
        std::vector<std::string>& delNames = _groupMap[groupName];  // 获取集群下的所有配置项名称

        std::vector<std::string> pathVec;   // 需要删除的线程的路径 
        // 每个配置项名称对应的控项 (若全部清空，则还需要关闭线程)
        for(int i = 0; i < delNames.size(); ++i) {
            std::string delName = delNames[i];  // 当前要删除的配置项名称
            for(std::map<std::string, RulePair>::iterator it = _ruleMap.begin(); it != _ruleMap.end(); ++it ) {
                RulePair& rules = it->second;
                int pos = rules.findName(delName);
                if(pos >= 0) {  // 找到      
                    // 获取rule的key
                    std::string match = rules._match;
                    std::string filt = rules._filt;
                    bool isAwk    = rules._isAwk;
                    bool isSearch = rules._isAwk;
                    std::string awkStr    = isAwk? "1" : "0";
                    std::string searchStr = isSearch? "1" : "0";
                    std::string key = match + "&" + filt + "&" + awkStr;

                    rules.eraseName(pos);                                   // 移除名字 
                    if(rules.isEmpty()) {                                   // 判断 _name字段是否空 若是，则移除这个rule
                        std::cout << "empty rules, erase" << std::endl;
                        _ruleMap.erase(key);
                        
                        // 移除rule之后还要判断_ruleMap是否为空 如果当前监听的路径没有rule了，就不再监听这个路径了, 将路径存入数组并返回，外层在调用之后进行对应的操作 (从全局配置中提出，并且终止相应的线程)
                        if(_ruleMap.empty()) {
                            // std::cout << "empty ruleMap, kill thread" << std::endl;
                            // 存入并返回
                            pathVec.push_back(_path);
                        }
                    }

                    break;   // 考虑到同名, 不要break
                }

            }

        }
        // 删除 groupMap对应的字段
        _groupMap.erase(groupName);
        return pathVec;
    }

};




/* 针对进程的配置 */
struct ProcPair {
    std::map<std::string, std::vector<std::string> > _ruleMap;     // procCmd   => names
    std::map<std::string, std::vector<std::string> > _groupMap;    // groupName => names
    std::map<std::string, std::vector<std::string> > _pathMap;     // startCmd  => names

    /*
    *   增加监控项
    *   集群名称、监控项名称、监控项进程的命令
    */
    void addRule(std::string groupName, std::string name, std::string procCmd, std::string startPath) {
        _ruleMap[procCmd].push_back(name);
        _groupMap[groupName].push_back(name);
	    if(!startPath.empty() ) {
	        _pathMap[startPath].push_back(name);
	    }
    }

    /*
    *   删除监控项 (以集群为单位)
    */
    void removeGroup(std::string groupName) {
        const std::vector<std::string>& delNames = _groupMap[groupName]; // 需要删除的当前集群下的监控项名称
        for(int i = 0; i < delNames.size(); ++i) {
            const std::string& delName = delNames[i];   // 要删除的监控项名称
            // 在ruleMap 中遍历查找 delName
            for(std::map<std::string, std::vector<std::string> >::iterator it = _ruleMap.begin(); it != _ruleMap.end(); ++it) {
                const std::string& rule               = it->first;
                std::vector<std::string>& names = it->second;
                size_t pos = findName(delName, names);
                // 找到
                if(pos != -1) {
                    // 移除监控项名称
                    names.erase(names.begin() + pos);          
                    // 判断监控项名称是否为空, 若为空， 则移除整个监控项
                    if(names.empty()) {
                        // std::cout << "0" << std::endl;
                        _ruleMap.erase(rule);
                    }
                    break;
                }
            }

        }
        _groupMap.erase(groupName);
    }

    /*
    *   DEBUG
    */
    void printConf() {
        std::cout << "--------proc conf------------" << std::endl;
        std::cout << "ruleMap:" << std::endl;
        for(std::map<std::string, std::vector<std::string> >::iterator it = _ruleMap.begin(); it != _ruleMap.end(); ++it) {
            const std::string& procCmd = it->first;
            const std::vector<std::string>& names = it->second;
            std::cout << "cmd:" << procCmd << std::endl;
            std::cout << "name:";
            for(size_t i = 0; i < names.size(); ++i) {
                std::cout << names[i] << ", ";
            }
            std::cout << std::endl;
        }
        std::cout << "groupMap:" << std::endl;
        for(std::map<std::string, std::vector<std::string> >::iterator it = _groupMap.begin(); it != _groupMap.end(); ++it) {
            const std::string& groupName = it->first;
            const std::vector<std::string>& names = it->second;
            std::cout << "group:" << groupName << std::endl;
            std::cout << "name:";
            for(size_t i = 0; i < names.size(); ++i) {
                std::cout << names[i] << ", ";
            }
            std::cout << std::endl;            
        }
        std::cout << "----------over---------------" << std::endl;
    }

private:
    size_t findName(const std::string& name, const std::vector<std::string>& names) {
        for(size_t i = 0; i < names.size(); ++i) {
            if(names[i] == name) {
                return i;
            }
        }
        return -1;
    }
};


/* 针对端口的配置 */
struct PortPair {
    std::map<std::string, std::vector<std::string> > _ruleMap;     // port   => names
    std::map<std::string, std::vector<std::string> > _groupMap;   // groupName  => names

    /*
    *   增加监控项
    *   集群名称、监控项名称、监控项端口号
    */
    void addRule(std::string groupName, std::string name, std::string port) {
        _ruleMap[port].push_back(name);
        _groupMap[groupName].push_back(name);
    }

    /*
    *   删除监控项 (以集群为单位)
    */
    void removeGroup(std::string groupName) {
        const std::vector<std::string>& delNames = _groupMap[groupName]; // 需要删除的当前集群下的监控项名称
        for(int i = 0; i < delNames.size(); ++i) {
            const std::string& delName = delNames[i];   // 要删除的监控项名称
            // 在ruleMap 中遍历查找 delName
            for(std::map<std::string, std::vector<std::string> >::iterator it = _ruleMap.begin(); it != _ruleMap.end(); ++it) {
                const std::string& rule               = it->first;
                std::vector<std::string>& names = it->second;
                size_t pos = findName(delName, names);
                // 找到
                if(pos != -1) {
                    // 移除监控项名称
                    names.erase(names.begin() + pos);          
                    // 判断监控项名称是否为空, 若为空， 则移除整个监控项
                    if(names.empty()) {
                        // std::cout << "0" << std::endl;
                        _ruleMap.erase(rule);
                    }
                    break;
                }
            }

        }
        _groupMap.erase(groupName);
    }

    /*
    *   DEBUG
    */
    void printConf() {
        std::cout << "--------port conf------------" << std::endl;
        std::cout << "ruleMap:" << std::endl;
        for(std::map<std::string, std::vector<std::string> >::iterator it = _ruleMap.begin(); it != _ruleMap.end(); ++it) {
            const std::string& port = it->first;
            const std::vector<std::string>& names = it->second;
            std::cout << "cmd:" << port << std::endl;
            std::cout << "name:";
            for(size_t i = 0; i < names.size(); ++i) {
                std::cout << names[i] << ", ";
            }
            std::cout << std::endl;
        }
        std::cout << "groupMap:" << std::endl;
        for(std::map<std::string, std::vector<std::string> >::iterator it = _groupMap.begin(); it != _groupMap.end(); ++it) {
            const std::string& groupName = it->first;
            const std::vector<std::string>& names = it->second;
            std::cout << "group:" << groupName << std::endl;
            std::cout << "name:";
            for(size_t i = 0; i < names.size(); ++i) {
                std::cout << names[i] << ", ";
            }
            std::cout << std::endl;            
        }
        std::cout << "----------over---------------" << std::endl;
    }

private:
    size_t findName(const std::string& name, const std::vector<std::string>& names) {
        for(size_t i = 0; i < names.size(); ++i) {
            if(names[i] == name) {
                return i;
            }
        }
        return -1;
    }
};



/**
 *  业务输出类
 */
class BusinessMap {
public:
    /* 初始化 */
    void init (const std::map<std::string, LogPair>& confMap) {
	    CMyLock lock = CMyLock(_mutex);  // lock
	    // 清理
	    _businessMapSum.clear();
	    _businessMapAve.clear();
        for(std::map<std::string, LogPair>::const_iterator it = confMap.begin(); it != confMap.end(); ++it) {
            const LogPair& logPair = it->second;
            // 匹配规则 name 作为 key 初始化 _businessMap
            for(std::map<std::string, RulePair>::const_iterator it1 = logPair._ruleMap.begin(); it1 != logPair._ruleMap.end(); ++it1) {
        		if(it1->second._isAwk) {	
		            for(std::vector<std::string>::const_iterator it2 = it1->second._nameVec.begin(); it2 != it1->second._nameVec.end(); ++it2) {
		 	            _businessMapAve[*it2] = std::vector<double>();			
                    }
		        }else if(it1->second._isSearch){
		            for(std::vector<std::string>::const_iterator it2 = it1->second._nameVec.begin(); it2 != it1->second._nameVec.end(); ++it2) {
                        _businessMapAve[*it2] = std::vector<double>();
                    }
                }else {
		            for(std::vector<std::string>::const_iterator it2 = it1->second._nameVec.begin(); it2 != it1->second._nameVec.end(); ++it2) {
		 	            _businessMapSum[*it2] = 0;			
                    }
		        }    
            }
        }
        // 设置时间
        _curtime = getTimeStamp();
    }

   // 判断是否应该输出
   bool check() {
       time_t time = getTimeStamp();
       if( (time - _curtime) >= 10 ) {
	       _curtime = time;
	       return true;
       }
       return false;
   }

   /* 增加一条记录(求和) */
   void increaseSum(const std::string& keyName) {
	   CMyLock lock = CMyLock(_mutex);  // lock
	   if(_businessMapSum.count(keyName) > 0) {
	       _businessMapSum[keyName] += 1;
	   }else {	
	       // std::cout << "key name :" << keyName << "does not existed" << std::endl;
	   }
   }

   /* 增加一条记录(求平均) */
   void increaseAve(const std::string& keyName, double val) {
	   CMyLock lock = CMyLock(_mutex);  // lock
	   if(_businessMapAve.count(keyName) > 0) {
	       _businessMapAve[keyName].push_back(val);
	       // std::cout << "existed, new val = " << _businessMapSum[keyName] << std::endl;
	   }else {	
	       // std::cout << "key name :" << keyName << "does not existed" << std::endl;
	   }
   }

   /* 输出字符串 */ 
   std::string getOutput() {
	   CMyLock lock = CMyLock(_mutex);  // lock
	   std::string retStr = "";
	   for(std::map<std::string, LINT>::iterator it = _businessMapSum.begin(); it != _businessMapSum.end(); ++it) {
	       retStr += it->first;
	       retStr += "@";
	       retStr += doubleToString((double)(it->second) / 10);
	       retStr += " "; 
	   }
	   
	   for(std::map<std::string, std::vector<double> >::iterator it = _businessMapAve.begin(); it != _businessMapAve.end(); ++it) {
	      // 计算平均值
	      double sum = 0;
          double max = 0;
	      for(std::vector<double>::iterator it1 = (it->second).begin(); it1 != (it->second).end(); ++it1) {
	          sum += *it1;	
              if(*it1 > max) {
                  max = sum;
              }
	      }
	      double ave = 0;
	      if( (it->second).size() > 0 ) {
	          ave = sum / (it->second).size();
	      }  
	      retStr += it->first;
	      retStr += "@";
	      retStr += doubleToString(ave);
          retStr += " "; 
          retStr += (it->first + "_max@");
	      retStr += doubleToString(max);
          retStr += " "; 
	   }
	   return retStr;
   }

private:
    std::map<std::string, LINT> _businessMapSum;		            // 和   (match)
    std::map<std::string, std::vector<double> > _businessMapAve;	// 平均-值 (awk)
    CMutex _mutex;
    time_t _curtime;   

};

