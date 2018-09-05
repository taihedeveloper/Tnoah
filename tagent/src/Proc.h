#pragma once
#include <unistd.h> 
#include <sys/types.h> 
#include <sys/wait.h> 
#include <stdio.h> 
#include <stdlib.h> 
#include <fcntl.h> 
#include <limits.h> 
#include <string>
#include "Business.h"

#define BUFSZ PIPE_BUF 

/* 
 * @desc   根据父进程pid获取所有子进程pid  
 * @param  string pid      父进程pid 
 * @param  bool   needSeld 返回值是否加上自身
 * @return vector<string>  父进程及其子进程的所有pid集合
*/
std::vector<std::string> getProcPids(const std::string& pid, bool needSelf = false) {
    std::vector<std::string> ret;
    char buf[5096];        
    const std::string cmd = "ps -ef | awk '{if(" + pid + "==$3){print $2}}'";
    int cmdRet = executeCMD(cmd.c_str(), buf, sizeof(buf));    
    if(true == needSelf) {
        ret.push_back(pid);
    }
    if(cmdRet != 0) {
        return ret;
    }   
    std::vector<double> dvec = digDigit(buf);
    for(size_t i = 0; i < dvec.size(); ++i) {
        ret.push_back(intToString(dvec[i]));
    }   
    return ret;
}

/**
*   获取进程数目 (大于1 说明存在)
*/  
int getProcNums(const std::string& taskName) {
    std::string command = "ps -ef |grep \"" + taskName + "\" |wc -l";
    std::cout << "[NOTICE] command:" << command << std::endl;
    FILE* fp = popen(command.c_str(), "r");
    char buf[BUFSZ];
    if( NULL == fp ){
        std::cout << "NULL" << std::endl;
        pclose(fp);        
        return 0;
    }
    if( NULL != (fgets(buf, BUFSZ, fp)) ) {
        int retCount = atoi(buf);
        pclose(fp);        
        return retCount;
    }
    pclose(fp);    
    return 0;
}

/**
*   传入进程名称, 判断进程是否存在
*/
bool checkProcExisted(const std::string& taskName) {
    int ret = getProcNums(taskName);
    if(ret > 2) {
        return true;
    }else {
        return false;
    }
}

/**
*  进程存在性校验
*/
std::string procExistedExec() {
    // 多线程, 这里一定不可以用引用
    std::map<std::string, std::vector<std::string> > procRule = g_procConf._ruleMap;
    std::string ret = "";
    int procCount = 1;
    if(procRule.size() >= 1) {
        procCount = procRule.size();
    }else {
        usleep(10000000);
        return "";
    }
    int sleepTime = 10000000 / procCount - 220000;
    for(std::map<std::string, std::vector<std::string> >::iterator it = procRule.begin(); it != procRule.end(); ++it) {
        const std::string& cmd = it->first;
        const std::vector<std::string> names = it->second;
        if(checkProcExisted(cmd)) {
            for(std::vector<std::string>::const_iterator vit = names.begin(); vit != names.end(); ++vit) {
                ret += (*vit + "@1 ");
            }
        }else {
            for(std::vector<std::string>::const_iterator vit = names.begin(); vit != names.end(); ++vit) {
                ret += (*vit + "@0 ");
            }
        }
        usleep(sleepTime);
    }
    return ret;
}

struct procPerformanceInfo {
public:
    double _cpuRate    ;
    double _memRate    ;
    LINT  _memRes      ;
    LINT   _handlerNum ;
public:
    void setInfo(double cpuRate, double memRate, LINT memRes, LINT handlerNum) {
        _cpuRate    = cpuRate;
        _memRate    = memRate;
        _memRes     = memRes;
        _handlerNum = handlerNum;
    }
    /* debug */
    void getInfo() {
        std::cout << "cpu rate:" << _cpuRate << std::endl;
        std::cout << "mem rate:" << _memRate << std::endl;
        std::cout << "mem res:"  << _memRes << std::endl;
        std::cout << "handler nums:" << _handlerNum << std::endl;
    }
 };



/*
 *  给定pid, 获取单个进程的cpu使用率/内存使用率/句柄数 (不合理, 弃用)
*/
procPerformanceInfo singleProcPerformance(const std::string& pid) {
    procPerformanceInfo retInfo;
    const std::string fdPath = "/proc/" + pid + "/fd";
    std::cout << fdPath << std::endl;
    LINT fdCount = getFilesNum(fdPath);
    // pfdCount
    // g_msgQue->push("12313");
    exit(0);
}

/*
 *  根据启动路径获取当前进程及所有子进程(如果有)的性能情况(cpu使用率/内存使用率/句柄数)
*/
procPerformanceInfo procPerformance(const std::string& path) {
    procPerformanceInfo retInfo;
    // 获取进程pid(父进程)
    char buf[10000];
    const std::string cmd = "ps -ef | grep \"" + path + "\" | awk {'if(1==$3){print $2}'}";
    int cmdRet = executeCMD(cmd.c_str(), buf, sizeof(buf));
    if(cmdRet != 0) {
        return retInfo;   
    }
    std::vector<std::string> pids;  // 要查询的所有pid
    SplitString(std::string(buf), pids, std::string("\n"));
    int masterCount = pids.size();  // 记录master进程数目
    for(size_t i = 0; i < masterCount; ++i) {
        // 获取每个master进程的worker进程
        std::vector<std::string> workerPids = getProcPids(pids[i]); 
        if(!workerPids.empty()) {
            int workerNums = workerPids.size();
            for(size_t j = 0; j < workerNums; ++j) {
                pids.push_back(workerPids[j]);
            }
        }
    }

    // 对pids进行去重
    std::map<std::string, int> tmpMap;
    size_t maxI = pids.size();
    std::vector<std::string> singlePids;  // 去重后的vector
    for(size_t i = 0; i < maxI; ++i) {
        const std::string& val = pids[i];
        if(tmpMap[val] >= 1) {
            continue;
        }else{
            tmpMap[val] = 1;
            singlePids.push_back(val);
        }
    }
    pids = singlePids;
    int pidsCount = pids.size();
    /*
        统计pids数组内所有进程的cpu/内存使用率/ 内存使用量, 目前的方式为直接执行如下命令
        ps aux | awk '{ if($2==4626||$2==8313) {MEM += $4;CPU += $3}}END{print CPU,MEM}'
    */
    std::string psCmd = "ps aux | awk '{if($2==";
    for(size_t i = 0; i < pidsCount - 1; ++i) {
        psCmd += (pids[i] + "||$2==");
    }
    psCmd += pids[pidsCount - 1];
    psCmd += "){CPURATE += $3; MEMRATE += $4; MEMRES += $6;}}END{print CPURATE, MEMRATE, MEMRES}'";

    memset(buf, '\0', sizeof(buf));
    cmdRet = executeCMD(psCmd.c_str(), buf, sizeof(buf));
    if(cmdRet != 0) {
        return retInfo;
    }
    std::vector<std::string> cmdRetVec;
    SplitString(buf, cmdRetVec, " ");
    const std::string& cpuRate = cmdRetVec[0];
    const std::string& memRate = cmdRetVec[1];
    const std::string& memRes = cmdRetVec[2];

    // 统计pids数组内所有进程创建的fd数目, 目前的方式是获取 /proc/pid/fd 路径下的文件数目
    LINT fdNums = 0;
    // 这种方式存在性能问题
    // for(size_t i = 0; i< pidsCount; ++i) {
    //     LINT fdCount = getFilesNum("/proc/" + pids[i] + "/fd"); 
    //     fdNums += fdCount;
    // }
    
    // 一样不好.. 舍弃
    // psCmd = "lsof -n | awk '{print $2}' | sort | uniq -c | awk '{if($2==";
    // for(size_t i = 0; i < pidsCount - 1; ++i) {
    //     psCmd += (pids[i] + "||$2==");
    // }
    // psCmd += pids[pidsCount - 1];
    // psCmd += "){FDNUM+=$1}}END{print FDNUM}'";
    // std::cout << psCmd << std::endl;
    // exit(0);

    // 最终采用批量ls -a 的方式获取fd数目
    std::string llCmd = "ls -l"; // "ll不可用" "ls -l可用"
    for(size_t i = 0; i < pidsCount - 1; ++i) {
        const std::string& pidStr = pids[i];
        int pidInt = atoi(pids[i].c_str());
        llCmd += (" /proc/" + intToString(pidInt) + "/fd");
    }
    llCmd += ("|wc -l");
    memset(buf, '\0', sizeof(buf));
    cmdRet = executeCMD(llCmd.c_str(), buf, sizeof(buf));
    if(cmdRet == 0) {  // 执行成功
        fdNums = atol(buf);
        fdNums = fdNums - 3 * pidsCount + 1;
    }
    if(fdNums <= 0) {
        std::cout << "[NOTICE] " << "cmd: " << llCmd << " get 0 or less result ! " << std::endl;
        fdNums = 0;
    }
    
    retInfo.setInfo(atof(cpuRate.c_str()), atof(memRate.c_str()), atoi(memRes.c_str()), fdNums);  
    retInfo.getInfo();
    return retInfo;
}

/*
 * 进程性能获取
*/
std::string procPerformanceExec(int gapTime = 3) {
    // 多线程, 这里一定不可以用引用
    const std::map<std::string, std::vector<std::string> >& procPerfRule = g_procConf._pathMap; 
    std::string ret = "";
    for(std::map<std::string, std::vector<std::string> >::const_iterator it = procPerfRule.begin(); it != procPerfRule.end(); ++it) {
        const std::string& path = it->first; 
        const std::vector<std::string> names = it->second;
        std::cout << "[NOTICE] " << path << std::endl;
        procPerformanceInfo retInfo = procPerformance(path);
        // product
        int namesCount = names.size();
        std::string procPerformanceStr = "";
        for(size_t i = 0; i < namesCount; ++i) {
            procPerformanceStr += (names[i] + "_cpurate@" + doubleToString(retInfo._cpuRate) + " ");
            procPerformanceStr += (names[i] + "_memrate@" + doubleToString(retInfo._memRate) + " ");
            procPerformanceStr += (names[i] + "_memres@"  + doubleToString(retInfo._memRes) + " ");
            procPerformanceStr += (names[i] + "_fdnum@"   + doubleToString(retInfo._handlerNum) + " ");
        } 
        g_msgQue->push(procPerformanceStr);
        sleep(gapTime);
    }
    sleep(1); // 当没有此类配置时, 如果不sleep, cpu会被打满
    return ret;
}
