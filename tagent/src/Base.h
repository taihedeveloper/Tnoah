#pragma once
#include <time.h>
#include <string>
#include <sstream>
#include <fstream>  
#include <sstream> 
#include <cstdio>
#include <cstring>
#include <iostream>
#include <sys/ioctl.h>
#include <net/if.h>
#include <arpa/inet.h>
#include <errno.h> 
#include <netdb.h> 
#include <sys/types.h> 
#include <netinet/in.h> 
#include <arpa/inet.h>
#include <stdlib.h>
#include <vector>
#include <netdb.h>
#include <regex.h>
#include <dirent.h>
#include <dirent.h>
#include <map>
#include "boost/regex.hpp"

typedef long long int LINT;

/* 获取时间戳 */
time_t getTimeStamp();

/* 根据时间戳获取时间(精确到小时) */
LINT timeStampToTime(time_t timeStamp, bool needHour = true, bool needMin = false, bool needSec = false);

/* 获取时间 */
std::string getTimeFormat();

/* 获取时间 (Year Month Day Hour Minutes Seconds) */
LINT getNowTime(bool needHour = true, bool needMin = false, bool needSec = false);

/* 获取下次更新时间 */
LINT getNextUpdateTime(LINT offset = 3600, bool needHour = true, bool needMin = false, bool needSec = false);

/* 获取本机IP */
std::string getLocalIp();

/* 获取本机 Hostname */
std::string getHostName();

/* 整形转字符串 */
std::string intToString(LINT n);

/* double转字符串 */
std::string doubleToString(double n);

/* 执行shell命令并保存结果 */
int executeCMD(const char *cmd, char *result, size_t sz);

/* DEBUG 打印vector的信息 */
void printVector(const std::vector<std::string>& svec);

/* 字符串分割 */
void SplitString(const std::string& s, std::vector<std::string>& v, const std::string& c);

/* 文件读入字符串 */
std::string readFileIntoString(const char * filename);

/* 正则匹配 */
bool doRegex(const std::string& content, const std::string& pattern);

/* 正则匹配 */
std::string  doRegexSearchBoost(const std::string& content, const std::string& pattern);

/* awk匹配 */
std::string doAwk(const std::string& content, int pos);      

/* 提取数字 */
std::vector<double> digDigit(const std::string& src);

/* 获取路径下的文件数目 */
LINT getFilesNum(const std::string& path);
