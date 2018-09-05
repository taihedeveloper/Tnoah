#include "Log.h"

Log::Log(const std::string item) {
    std::string path = "./logs/" + item + ".log";
    fLog = fopen(path.c_str(), "a+");
}

Log::~Log() {
        // if(!_logStr.empty()) {
        //     fprintf(fLog, "%s\n", _logStr.c_str());    
        // }
        fclose(fLog);   
}

void Log::append(const char* logStr, bool sout) {
    std::string tmp = std::string("[NOTICE] ") + std::string(logStr);
    if(sout) {
	std::cout << tmp << std::endl;
    }
    if(!tmp.empty()) {
        fprintf(fLog, "%s\n", tmp.c_str());    
    }
}

void Log::appendFatal(const char* logStr, bool sout) {
    std::string tmp = std::string("[FATAL] ") + std::string(logStr);
    _logStr = tmp;
    if(sout) {
	std::cout << tmp << std::endl;
    } 
    if(!tmp.empty()) {
        fprintf(fLog, "%s\n", tmp.c_str());    
    }
}     
      
void Log::flush() {
    fflush(fLog);
}
