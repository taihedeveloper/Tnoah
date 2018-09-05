#include "Base.h"
/*
*   获取当前系统时间戳
*   @return  time_t
*/
typedef long long int LINT;
time_t getTimeStamp() {
    time_t t;
    time(&t);
    return t;
}

/*
*   根据时间戳获取时间(精确到小时) 
*/
LINT timeStampToTime(time_t timeStamp, bool needHour, bool needMin, bool needSec){
        struct tm* info = localtime(&timeStamp);
        std::string year = intToString(info->tm_year + 1900);
        std::string month = intToString(info->tm_mon + 1);
        std::string day   = intToString(info->tm_mday);
        std::string hour = intToString(info->tm_hour);
        std::string minute = intToString(info->tm_min);
        std::string second = intToString(info->tm_sec);
        if(month.size() == 1) {
            month = "0" + month;
        }
        if(day.size() == 1) {
            day = "0" + day;
        }
        if(hour.size() == 1) {
            hour = "0" + hour;
        }
        if(minute.size() == 1) {
            minute = "0" + minute;
        }
        if(second.size() == 1) {
            second = "0" + second;
        }
        std::string retString = year + month + day;
        if(needHour) {
            retString += hour;
            if(needMin) {
                retString += minute;
                if(needSec) {
                    retString += second;
                }
            }
        }
    
        LINT curTime = strtol(retString.c_str(), NULL, 10);
        return curTime;
    }

/*
*   获取当前系统时间
*   @return  std::string
*/  
std::string getTimeFormat() {
    time_t t;
    time(&t);
    struct tm* now = localtime(&t);
    std::string retStr = asctime(now);
    return retStr;
}

/*
*   获取时间(Time Month Day Hour Minutes (Seconds) )
*/
LINT getNowTime(bool needHour, bool needMin, bool needSec) {
    time_t timeStamp = getTimeStamp();
    LINT curTime = timeStampToTime(timeStamp, needHour, needMin, needSec);
    return curTime;
}

/*
*   获取下次更新时间
*   param:  offset(s)
*/
LINT getNextUpdateTime(LINT offset, bool needHour, bool needMin, bool needSec) {
    time_t timeStamp = getTimeStamp() + offset;
    LINT nextTime = timeStampToTime(timeStamp, needHour, needMin, needSec);    
    return nextTime;
}

/*
 *    执行系统命令获取ip  (通常不用, 只有传统方法获取不到ip时才会用这种方式试探)
 */
std::string cmdExecIp(const std::string& cmd) {
    FILE* fp = popen(cmd.c_str(), "r");
    char buf[2048];
    if( NULL == fp ){
        std::cout << "NULL" << std::endl;
        pclose(fp);
        return "";
    }
    if( NULL != (fgets(buf, sizeof(buf), fp)) ) {
       	std::string retVal = std::string(buf);
	if(retVal.size() >= 4) {
	    return retVal;
        }
    }
    pclose(fp);
    return "";
}

/*
*   获取本机IP
*/
std::string getLocalIp() {        
        int result           = 0;
        char hostName[100]   = {0};
        char ip[32]          = {0};
        struct hostent* host = NULL;
        result = gethostname(hostName, 100);
        host = gethostbyname(hostName);
	if(!host) {
	    // 传统的方式无法获取到ip, 走这里  (至少避免崩溃)
	    std::cout << "FATAL get ip address fail ! tagent now use shell commend to get a ip addr but it's not reliable" << std::endl;;
	    std::string cmd = "ifconfig eth0 | grep \"inet addr\" | awk '{ print $2}' | awk -F: '{print $2}'";
	    std::string ip_maybe = cmdExecIp(cmd);
	    if(ip_maybe.empty()) {
		return "0.0.0.0";
	    }	
	    // 去掉最末尾的 '\n'
	    ip_maybe = ip_maybe.substr(0, ip_maybe.size() - 1);
	    return ip_maybe;
	}
        return std::string(inet_ntoa(*((struct in_addr *)host->h_addr_list[0])));
} 

/*
*   整形转字符串
*/
std::string intToString(LINT n) {
    std::ostringstream stream;
    stream << n;
    return stream.str();
}

/*
*    double 转字符串
*/
std::string doubleToString(double n) {
    // std::ostringstream stream;
    // stream << n;
    // return stream.str();
    /* 之前一直用上面的方式, 但发现当n长度较大时会返回一个科学计数法格式的字符串, 导致下游spark崩溃 */
    char buf[512];
    sprintf(buf, "%f", n); 
    return std::string(buf);
}

/*
*   执行shell命令,并保存结果
*   @return   0 / -1
*/
/////////////// 8_17
int executeCMD(const char *cmd, char *result, size_t sz) { 
    char buf_ps[1024];   
    FILE* ptr;   
    if((ptr=popen(cmd, "r"))!=NULL) {   // 执行shell命令并返回标准IO流
        while(fgets(buf_ps, sizeof(buf_ps), ptr)!=NULL) {   
            if(strlen(result) >= sz) {
                break;   
            }
           strcat(result, buf_ps);   
        }   
        pclose(ptr);   
        ptr = NULL;  
        return 0; 
    }
    else {   
        return -1;
    }   
}

/**
*   打印vector信息
*/
void printVector(const std::vector<std::string>& svec) {
    for(size_t i = 0; i < svec.size(); ++i) {
        std::cout << svec[i] << ", ";
    }
    std::cout << std::endl;
}

/**
*   字符串分割
*/
void SplitString(const std::string& s, std::vector<std::string>& v, const std::string& c)
{
    std::string::size_type pos1, pos2;
    pos2 = s.find(c);
    pos1 = 0;
    while(std::string::npos != pos2) {
        v.push_back(s.substr(pos1, pos2-pos1));
        pos1 = pos2 + c.size();
        pos2 = s.find(c, pos1);
    }
    if(pos1 != s.length()) {
        v.push_back(s.substr(pos1));
    }
}

/**
*   从文件读入到string里  
*/
std::string readFileIntoString(const char * filename)  
{  
    std::ifstream ifile(filename);  
    //将文件读入到ostringstream对象buf中  
    std::ostringstream buf;  
    char ch;  
    while(buf&&ifile.get(ch))  
    buf.put(ch);  
    //返回与流对象buf关联的字符串  
    return buf.str();  
}  

/* 获取本机 Hostname */
std::string getHostName() {
    struct hostent *he;
    char hostname[80]={0} ;
    char ipaddr[80]={0};

    gethostname(hostname,sizeof(hostname));
    return std::string(hostname);
}

/*
*   正则匹配
*/
bool doRegex(const std::string& content, const std::string& pattern) {
    // 性能优化, 通配符不会每次都经历编译、创建、销毁的步骤
    static std::map<std::string, regex_t> regMap;
    regex_t reg;
    if(regMap.find(pattern) != regMap.end()) {  // 存在, 直接使用
        // std::cout << "[NOTICE] key" << pattern << " exist, use do regex" << std::endl;
        reg = regMap[pattern];
    }else {  // 不存在, 编译, 并存入内存
        // std::cout << "[NOTICE] key" << pattern << " not exist, create new while do regex" << std::endl;
        int ret = regcomp(&reg, pattern.c_str(), REG_EXTENDED);
        if(ret != 0) {
            return false;
        }
        regMap[pattern] = reg;
    }
    regmatch_t pmatch[1];
    int status = regexec(&reg, content.c_str(), 1, pmatch, 0);
    if(status == REG_NOMATCH) {
        return false;
    }else {
        return true;
    }

    // 老的正则匹配逻辑, 性能较低已经弃用
    // regex_t reg;
    // regcomp(&reg, pattern.c_str(), REG_EXTENDED);
    // regmatch_t pmatch[1];
    // int status = regexec(&reg, content.c_str(), 1, pmatch, 0);
    // regfree(&reg);
    // if(status == REG_NOMATCH) {
    //     return false;
    // }else {
    //     return true;
    // }
}

/*
 * *   正则查找(boost regex_search)
 * */
std::string doRegexSearchBoost(const std::string& content, const std::string& pattern) {
    static std::map<std::string, boost::regex> regSearchMap;
    boost::regex reg;
    if(regSearchMap.find(pattern) != regSearchMap.end()) {
        reg = regSearchMap[pattern];
    }else {  // 不存在, 编译
        reg = boost::regex(pattern);
        regSearchMap[pattern] = reg;
    }
    // 查找
    boost::smatch what;
    
    if(boost::regex_search(content, what, reg) && what.size() >= 2) {
        return what[1];
    }else {
        return "";
    }
}

/*
*   awk匹配 []   （平均型）
*   没有匹配到，或者匹配到的是不是数字，返回""
*/
std::string doAwk(const std::string& content, int pos) {
    if(pos == 0) {
        return content;
    }
    pos -= 1;
    if(pos < 0) {
        return "";
    }
    std::vector<std::string> tmpVec; 
    SplitString(content, tmpVec, " ");
    if(pos >= tmpVec.size()) {
        return "";
    }
    std::string answer = tmpVec[pos];
    size_t maxI = answer.size();
    for(size_t i = 0; i < maxI; ++i) {
        if(answer[i] > '9' || answer[i] < '0') {
            if(answer[i] != '.') {
                return "";
            }
        }
    }
    return answer;
}

/*
*   读取字符串,提取其中的所有数字(包括小数)
*   @param  字符串
*   @return 数字的集合
*/
std::vector<double> digDigit(const std::string& src) {
    std::vector<double> ret;
    std::string num = "";
    bool hasPoint = false;
    for(std::string::const_iterator s_it = src.begin(); s_it != src.end(); ++s_it) {
        char cur = *s_it;
        if( (cur >= '0' && cur <= '9') || cur == '.') {
            if(cur == '.') {  // 小数点
                if(hasPoint) {
                    num = "";
                    continue;
                }else {
                    hasPoint = true;
                    num += cur;
                }
           }else {  // 数字
               num += cur;
           }

        }else {
            if(num != "") {
                // std::cout << num << std::endl;
                ret.push_back(atof(num.c_str()));
                hasPoint = false;
                num = "";
            }
        }
    }
    if(num != "") {
        // std::cout << num << std::endl;
        ret.push_back(atof(num.c_str()));
    }
    return ret;
}
/*
 *  获取指定路径下的文件数目
 *  @param  path 
 *  @return vector
*/
LINT getFilesNum(const std::string& path) {
    LINT totalNum = 0;
    DIR* pD;
    struct dirent *entry;
    pD = opendir(path.c_str());
    if(NULL == pD) { // maybe target proc not existed
        return 0;
    }
    while((entry=readdir(pD)) != NULL) {
        if(entry->d_name[0] == '.') {
            continue;
        }
        ++totalNum; 
    } 
    return totalNum;
}
