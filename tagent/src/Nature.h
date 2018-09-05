/*
*   机器硬件性能监控
*/
#pragma once
#include <iostream>
#include <fstream>
#include <string>
#include <unistd.h>
#include <cstdio>
#include <string.h>
#include <ctime>
#include <cstdlib>
#include <vector>
#include <mntent.h>
#include <sys/vfs.h>
#include <cassert>
#include <sys/socket.h>
#include <sys/ioctl.h>
#include <netinet/in.h>
#include <net/if.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <ifaddrs.h>
#include <arpa/inet.h>



#include "Base.h"

typedef long long int LINT;

// 内存
struct VmStat {
    bool _avaliable;
    LINT _r    ;
    LINT _b    ;
    LINT _swpd ;
    LINT _free ;
    LINT _buff ;
    LINT _cache;
    LINT _si   ;
    LINT _so   ;
    LINT _bi   ;
    LINT _bo   ;
    LINT _in   ;
    LINT _cs   ;
    LINT _us   ;
    LINT _sy   ;
    LINT _id   ;
    LINT _wa   ;
    LINT _st   ;
    void init(const char* buf);
};

VmStat getVmStat();

std::string memoryExec();

std::string memoryExec_new();


// 交换分区
std::string swapExec(LINT swap_in, LINT swap_out);

// 磁盘
class DriveInfo {
public:
    std::string _drive;  // 分区
    std::string _type;   // 分区类型
    LINT _total;         
    LINT _used;
    LINT _available;
    double _useRate;
    LINT _inodeTotal;
    LINT _inodeAvailable;
    LINT _inodeUsed;
    double _inodeUseRate;

public:
    DriveInfo(const char* drive,const char* type)
        :_drive(drive)
        ,_type(type) {}

    void setBaseInfo(const char* drive,const char* type) {
        _drive = drive;
        _type  = type;
    }

    /* 数据信息 */
    void setDataInfo(LINT total, LINT used, LINT avaliable, double useRate, LINT inodeTotal, LINT inodeAvailable, LINT inodeUsed, double inodeUseRate) {
        _total          = total;
        _used           = used;
        _available      = avaliable;
        _useRate        = useRate;
        _inodeTotal     = inodeTotal;
        _inodeAvailable = inodeAvailable;
        _inodeUsed      = inodeUsed;
        _inodeUseRate   = inodeUseRate;
    }
};

struct statfs getDiskData(char* diskName);

std::string DiskTest();




// CPU
class CpuInfo {
public:
CpuInfo(char* buf, size_t sz, LINT in, LINT cs);
LINT getTotal();
void printInfo();

public:
char name[20];
LINT user;      // 用户态CPU时间
LINT nice;      // nice为负的CPU时间
LINT system;    // 内核态CPU时间
LINT idle;      // 除IO外其他等待CPU时间
LINT iowait;    // io等待时间 
LINT irq;       // 硬中断时间
LINT softirq;   // 软中断时间
LINT in;	    // 硬中断的次数
LINT cs;	    // 上下文切换的次数
};

/*
*   获取当前系统时间
*   @return  std::string
*/
std::string getTime();

/*
*   从/proc/stat文件中获取CPU信息
*/
CpuInfo* getCpuInfo();

/*
*   从/proc/loadavg文件中获取CPU信息 (5分钟平均负载)
*/
double getLoadAvg();

/*
*   获取Cpu使用率
*   @param  float   interval 采样间隔(毫秒)
*   @param  double* states   存放各状态下使用率的数组 
*   @param  LINT    in       cpu中断次数
*   @return float  使用率
*/
double getCpuRate(LINT& in, LINT& cs, int interval, double* states);

std::string cpuExec(int interval, LINT in, LINT cs);


// 进程调度
std::string procsExec(LINT procs_running, LINT procs_blocked, LINT procs_swapped);


// I/O
std::string ioExec(LINT block_in, LINT block_out);

struct Iostat {
    double _rrqm;
    double _wrqm; 
    double _r; 
    double _w; 
    double _rsec; 
    double _wsec; 
    double _rKb; 
    double _wKb; 
    double _avgrq_sz; 
    double _avgqu_sz; 
    double _await; 
    double _svctm; 
    double _util; 
};


// 网卡
std::string netInfos(int gapTime);
