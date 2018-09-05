#pragma once
#include <iostream>
#include <string>
#include <cstdio>
#include "Base.h"

class Log {
public:
    Log(const std::string item);

    ~Log();

    void append(const char* logStr, bool sout = false);

    void appendFatal(const char* logStr, bool sout = false);

    void flush();
    
private:
    std::string _logStr;
    FILE* fLog;
};