#pragma once
#include <unistd.h> 
#include <sys/types.h> 
#include <sys/wait.h> 
#include <stdio.h> 
#include <stdlib.h> 
#include <fcntl.h> 
#include <limits.h> 
#include <string>

#define BUFSZ PIPE_BUF

/**
*   获取指定端口列出信息长度 
*/
int getPortNums(std::string portNum) {
    // std::string command = "netstat -anp |grep " + portNum + " |wc -l";
    // 查找替换(0.0.0.0)
    int pos = portNum.find("0.0.0.0");
    if(pos >= 0) {
    	portNum = portNum.replace(pos, 7, "*");
    }
    // 补全(*:)
    // pos = portNum.find("*:");
    // if(pos == std::string::npos) {
    //     portNum = "*:" + portNum;
    // }
    
    std::string command = "ss -atun |grep " + portNum + " |wc -l";
    std::cout << command << std::endl;
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
*   传入端口号，判断端口号是否被占用
*/
bool checkPortExisted(const std::string& portNum) {
    int ret = getPortNums(portNum);
    if(ret > 0) {
        return true;
    }else {
        return false;
    }
}

/**
*   根据配置获取端口号信息
*/
std::string portExec() {
    // 多线程，一定不可以引用
    std::map<std::string, std::vector<std::string> > portRule = g_portConf._ruleMap;
    std::string ret = "";
    int portCount = 1;
    if(portRule.size() >= 1) {
        portCount = portRule.size();
    }else {
        usleep(10000000);
        return "";
    }
    int sleepTime = 10000000 / portCount - 200000;
    for(std::map<std::string, std::vector<std::string> >::iterator it = portRule.begin(); it != portRule.end(); ++it) {
        const std::string& cmd = it->first;
        const std::vector<std::string> names = it->second;
        if(checkPortExisted(cmd)) {
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
