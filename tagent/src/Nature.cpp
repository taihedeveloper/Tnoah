#include "Nature.h"

/* Memory and Swap */
/* 修改
*   由于不同机器上 meminfo不同信息的位置是不一样的 
*   因此之前那种固定查询的方式是错误的
*   顺便把交换内存的前3个字段也写进来
*/
std::string memoryExec() {
    std::ifstream infile;
    infile.open("/proc/meminfo", std::ios::in);
    long int MEM_BUFFERS; // 系统buffers大小 (KB)
    long int MEM_CACHED; // 系统Cached大小  (KB)
    long int MEM_FREE;   // 可用内存大小    (KB)
    long int MEM_TOTAL;  // 总内存大小      (KB)

    long int SWAP_TOTAL;  // 交换分区总量
    long int SWAP_FREE;   // 交换分区空余量 （不需要返回）
    // 读取, 处理
    while(!infile.eof()) {
        std::string line;
        getline(infile, line);
        char name[32];  // 没用，就是个占位符
        char buf[128];
        if(line.find("MemTotal") != std::string::npos) {
            strcpy(buf, line.c_str());
            sscanf(buf, "%s %ld %s", name, &MEM_TOTAL, name);
        }else if(line.find("MemFree") != std::string::npos) {
            strcpy(buf, line.c_str());          
            sscanf(buf, "%s %ld %s", name, &MEM_FREE, name);            
        }else if(line.find("Buffers") != std::string::npos) {
            strcpy(buf, line.c_str());   
            sscanf(buf, "%s %ld %s", name, &MEM_BUFFERS, name);            
        }else if(line.find("Cached") != std::string::npos && line.find("SwapCached") == std::string::npos) {
            strcpy(buf, line.c_str());  
            sscanf(buf, "%s %ld %s", name, &MEM_CACHED, name);      
        }else if(line.find("SwapTotal") != std::string::npos) {
            strcpy(buf, line.c_str());   
            sscanf(buf, "%s %ld %s", name, &SWAP_TOTAL, name);            
        }else if(line.find("SwapFree") != std::string::npos) {
            strcpy(buf, line.c_str());
            sscanf(buf, "%s %ld %s", name, &SWAP_FREE, name);            
        }
    }
    MEM_FREE = MEM_FREE + MEM_CACHED + MEM_BUFFERS;  
    long int MEM_USED = MEM_TOTAL - MEM_FREE;  // 内存使用量
    float MEM_USED_PERCENT = ((double)MEM_USED / MEM_TOTAL) * 100;    // 内存使用率
    float MEM_FREE_PERCENT = 100 - MEM_USED_PERCENT;
    long int MEM_FREE_SUB_SHMEM = MEM_FREE;    // 内存空闲量
    long int MEM_USED_ADD_SHMEM = MEM_USED;    // 内存使用量
    float MEM_USED_ADD_SHMEM_PERCENT = MEM_USED_PERCENT;
    float MEM_FREE_SUB_SHMEM_PERCENT = 100 - MEM_USED_PERCENT;

    long int SWAP_USED = SWAP_TOTAL - SWAP_FREE;    // 交换分区使用量
    double SWAP_USED_PERCENT = 0;
    if(SWAP_TOTAL > 0) {
        SWAP_USED_PERCENT = ((double)SWAP_USED / SWAP_TOTAL) * 100;
    }

    char buffer[512];
    sprintf(buffer, "MEM_BUFFERS@%ld MEM_CACHED@%ld MEM_FREE@%ld MEM_TOTAL@%ld MEM_USED@%ld MEM_FREE_PERCENT@%lf MEM_USED_PERCENT@%lf MEM_FREE_SUB_SHMEM@%ld MEM_USED_ADD_SHMEM@%ld MEM_USED_ADD_SHMEM_PERCENT@%lf MEM_FREE_SUB_SHMEM_PERCENT@%lf", MEM_BUFFERS, MEM_CACHED, MEM_FREE, MEM_TOTAL, MEM_USED, MEM_FREE_PERCENT, MEM_USED_PERCENT, MEM_FREE_SUB_SHMEM, MEM_USED_ADD_SHMEM, MEM_USED_ADD_SHMEM_PERCENT, MEM_FREE_SUB_SHMEM_PERCENT);
    std::string memStr = std::string(buffer);

    memset(buffer, '\0', sizeof(buffer));
    sprintf(buffer, "SWAP_FREE@%ld SWAP_TOTAL@%ld SWAP_USED@%ld SWAP_USED_PERCENT@%lf",SWAP_FREE, SWAP_TOTAL, SWAP_USED, SWAP_USED_PERCENT);

    std::string swapStr = std::string(buffer);

    infile.close();
    return memStr + " " + swapStr;

}


/* VmStat初始化 */
void VmStat::init(const char* buf) {
    std::string str = std::string(buf);
    std::string VmStr = str.substr(163, 86);
    // vmstat写入缓存
    char buffer[128];
    strcpy(buffer, VmStr.c_str());
    sscanf(buffer, "%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld\t%lld", &_r, &_b, &_swpd, &_free, &_buff, &_cache, &_si, &_so, &_bi, &_bo, &_in, &_cs, &_us, &_sy, &_id, &_wa, &_st);
    _avaliable = true;
}

/* 获取vmstat命令的信息 */
VmStat getVmStat() {
    char buf[10000];
    VmStat retVmStat = VmStat();
    int retVal = executeCMD("vmstat", buf, sizeof(buf));
    if(retVal != 0) {   // shell命令调用失败
        retVmStat._avaliable = false;
    }else {
        retVmStat.init(buf);    
    }
    return retVmStat;
}

// 交换分区
std::string swapExec(LINT swap_in, LINT swap_out) {
    std::string swapStr = "SWAP_IN@" + intToString(swap_in) + " " + "SWAP_OUT@" + intToString(swap_out);
    return swapStr;
}

// 磁盘
struct statfs getDiskData(char* diskName) {
    struct statfs sfs;
    statfs(diskName, &sfs);
    return sfs;
}

std::string DiskTest() {
    std::vector<DriveInfo> driveVec;
    struct mntent* m;
    FILE* f = NULL;
    f = setmntent("/etc/fstab", "r");
    assert(f);
    /* 最大值 */
    double MaxUseRate = 0;
    size_t MaxI = 0;    // 做索引
    double MaxInodeUseRate = 0;
    size_t MaxInodeI = 0;
    /* 总值 */
    LINT STotal = 0;
    LINT SAvailable = 0;
    LINT SInodeTotal = 0;
    LINT SInodeAvailable = 0;
    size_t idx = 0;
    while(m = getmntent(f)) {
        DriveInfo driveInfo(m->mnt_dir, m->mnt_type);
        /* 大小相关 */
        struct statfs sfs = getDiskData(m->mnt_dir);

        LINT Total = 4*sfs.f_blocks;
        LINT Used  = 4*(sfs.f_blocks - sfs.f_bfree);
        LINT Available = 4*sfs.f_bavail;

        /* 文件节点 */
        LINT InodeTotal      = sfs.f_files;
        LINT InodeAvailable  = sfs.f_ffree;

        double UseRate = 0;
        if((sfs.f_blocks -sfs.f_bfree + sfs.f_bavail) != 0) {
            UseRate = ((double)sfs.f_blocks - sfs.f_bfree ) * 100 / (sfs.f_blocks -sfs.f_bfree + sfs.f_bavail) + 1;
        }
        LINT InodeUsed = InodeTotal - InodeAvailable;
        double InodeUseRate = 0;
        if(InodeTotal != 0) {
            InodeUseRate = (double)InodeUsed / InodeTotal * 100;
        }

        driveInfo.setDataInfo(Total, Used, Available, UseRate, InodeTotal, InodeUsed, InodeAvailable, InodeUseRate);
        driveVec.push_back(driveInfo);

        // 处理最大值、总值
        STotal          += Total;
        SAvailable      += Available;
        SInodeTotal     += InodeTotal;
        SInodeAvailable += InodeAvailable;

        if(MaxUseRate < UseRate) {
            MaxUseRate = UseRate;
            MaxI = idx;
        }
        if(MaxInodeUseRate < InodeUseRate) {
            MaxInodeUseRate = InodeUseRate;
            MaxInodeI = idx;
        }
        ++idx;
    }

    // 拼字符串
    std::string DiskInfoStr;
    for(size_t i = 0; i < driveVec.size(); ++i) {
        DiskInfoStr += ("DISK_" + driveVec[i]._drive +"_TOTAL@" + intToString(driveVec[i]._total) + " ");
        DiskInfoStr += ("DISK_" + driveVec[i]._drive + "_FREE@" + intToString(driveVec[i]._available) + " ");
        DiskInfoStr += ("DISK_" + driveVec[i]._drive + "_USED@" + intToString(driveVec[i]._used) + " ");
        DiskInfoStr += ("DISK_" + driveVec[i]._drive + "_USED_PERCENT@" + intToString(driveVec[i]._useRate) + " ");
        // DiskInfoStr += ("DISK_" + driveVec[i]._drive + "_FS_TYPE@" + driveVec[i]._type + " ");
        
        DiskInfoStr += ("DISK_" + driveVec[i]._drive +"_INODE_TOTAL@" + intToString(driveVec[i]._inodeTotal) + " ");
        DiskInfoStr += ("DISK_" + driveVec[i]._drive + "_INODE_FREE@" + intToString(driveVec[i]._inodeAvailable) + " ");
        DiskInfoStr += ("DISK_" + driveVec[i]._drive + "_INODE_USED@" + intToString(driveVec[i]._inodeUsed) + " ");        
        DiskInfoStr += ("DISK_" + driveVec[i]._drive + "_INODE_USED_PERCENT@" + intToString(driveVec[i]._inodeUseRate) + " ");

    }

    // 最大值和总值
    DiskInfoStr += ("DISK_MAX_PARTITION_USED_PERCENT@" + doubleToString(MaxUseRate) + " ");
    // DiskInfoStr += ("DISK_MAX_USED_PERCENT_MNTDIR@" + driveVec[MaxI]._drive + " ");
    // DiskInfoStr += ("DISK_MAX_USED_PERCENT_FSNAME@" + driveVec[MaxI]._type + " ");
    DiskInfoStr += ("DISK_TOTAL@" + intToString(STotal) + " ");
    DiskInfoStr += ("DISK_TOTAL_FREE@" + intToString(SAvailable) + " ");
    DiskInfoStr += ("DISK_TOTAL_USED@" + intToString(STotal - SAvailable) + " ");
    double totalUseRate = ((double)STotal - SAvailable) / (STotal) * 100;
    DiskInfoStr += ("DISK_TOTAL_USED_PERCENT@" + doubleToString(totalUseRate) + " ");

    DiskInfoStr += ("DISK_MAX_PARTITION_INODE_USED_PERCENT@" + doubleToString(MaxInodeUseRate) + " ");
    // DiskInfoStr += ("DISK_MAX_INODE_USED_PERCENT_MNTDIR@" + driveVec[MaxInodeI]._drive + " ");
    // DiskInfoStr += ("DISK_MAX_INODE_USED_PERCENT_FSNAME@" + driveVec[MaxInodeI]._type + " ");
    DiskInfoStr += ("DISK_TOTAL_INODE@" + intToString(SInodeTotal) + " ");
    DiskInfoStr += ("DISK_TOTAL_INODE_FREE@" + intToString(SInodeAvailable) + " ");
    DiskInfoStr += ("DISK_TOTAL_INODE_USED@" + intToString(SInodeTotal - SInodeAvailable) + " ");

    double totalInodeUseRate = ((double)SInodeTotal - SInodeAvailable) / SInodeTotal * 100;
    DiskInfoStr += ("DISK_TOTAL_INODE_USED_PERCENT@" + doubleToString(totalInodeUseRate));
    endmntent(f);
    // fclose(f);
    return DiskInfoStr;
}

// DISK_IO
std::string DiskIoInfos() {
    return "";
}

// CPU
CpuInfo::CpuInfo(char* buf, size_t sz, LINT in, LINT cs) {
    sscanf(buf, "%s %lld %lld %lld %lld %lld %lld %lld", name, &user, &nice, &system, &idle, &iowait, &irq, &softirq);
    this->in = in;
    this->cs = cs;
}



/* 获取总时间 */
LINT CpuInfo::getTotal() {
    return (user + nice + system  + idle + iowait + irq + softirq);
}

/* 获取信息(DEBUG) */
void CpuInfo::printInfo() {
    std::cout << name << std::endl;
    std::cout << user << std::endl;
    std::cout << nice << std::endl;
    std::cout << system << std::endl;
    std::cout << idle << std::endl;
    std::cout << iowait << std::endl;
    std::cout << irq << std::endl;
    std::cout << softirq << std::endl;
}

/*
*   从/proc/stat文件中获取CPU信息
*/
CpuInfo* getCpuInfo() {
    FILE* fp = fopen("/proc/stat", "r");
    char buf[256];
    char buftmp[1024];
    fgets(buf, sizeof(buf)-1, fp);
    LINT in = 0;  // 中断次数
    LINT cs = 0;  // 上下文切换次数
    // 获取中断次数
    fgets(buftmp, sizeof(buftmp)-1, fp);
    while(NULL == strstr(buftmp, "intr")) {
        fgets(buftmp, sizeof(buftmp)-1, fp);
    }
    char space[256]; // 占位
    sscanf(buftmp, "%s %lld", space, &in);
    // 获取上下文切换次数
    fseek(fp, 0, SEEK_SET);
    fgets(buftmp, sizeof(buftmp)-1, fp);
    while(NULL == strstr(buftmp, "ctxt")) {
        fgets(buftmp, sizeof(buftmp)-1, fp);
    }
    sscanf(buftmp, "%s %lld", space, &cs);

    CpuInfo* cpuInfo = new CpuInfo(buf, sizeof(buf), in, cs);

    fclose(fp);
    return cpuInfo;
}

/*
*   从/proc/loadavg文件中获取CPU信息  
*   return : 5分钟平均负载
*/
double getLoadAvg() {
    FILE* fp = fopen("/proc/loadavg", "r");
    char buf[32];
    /* 占位符 */
    double space1;
    double space2;
    double space3;
    double space4;
    
    fgets(buf, sizeof(buf)-1, fp);
    fclose(fp);    
    double ret = 0;

    sscanf(buf, "%lf %lf %lf %lf %lf\n",&space1, &ret, &space2, &space3, &space4);
    return ret;

}


/*
*   获取Cpu使用率
*   @param  int   interval 采样间隔(毫秒)
*   @param  double* states   存放各状态下使用率的数组 
*   @return float  总体使用率
*/
double getCpuRate(LINT& in, LINT& cs,int interval = 100, double* states = NULL) {
    interval         = 1000 * interval;       // 转换成毫秒
    CpuInfo* cpuInfo1 = getCpuInfo();          // 第1次采样
    usleep(interval);
    CpuInfo* cpuInfo2 = getCpuInfo();          // 第2次采样
    LINT allTime     = cpuInfo2->getTotal() - cpuInfo1->getTotal();   // 总时间      
    LINT idleTime    = cpuInfo2->idle - cpuInfo1->idle;               // 空闲时间
    double cpuRate   = 1 - (double)idleTime / allTime;              // CPU总使用率

    if(states != NULL) {
        states[0] = ((double)cpuInfo2->user    - (double)cpuInfo1->user)    / allTime;    // 用户态时间使用率
        states[1] = ((double)cpuInfo2->nice    - (double)cpuInfo1->nice)    / allTime;    // nice为负的时间使用率
        states[2] = ((double)cpuInfo2->system  - (double)cpuInfo1->system)  / allTime;    // 内核态时间使用率
        states[3] = ((double)cpuInfo2->idle    - (double)cpuInfo1->idle)    / allTime;    // 除IO外其他等待CPU时间 (空闲时间)比率
        states[4] = ((double)cpuInfo2->iowait  - (double)cpuInfo1->iowait)  / allTime;    // IO等待时间比率
        states[5] = ((double)cpuInfo2->irq     - (double)cpuInfo1->irq)     / allTime;    // 硬中断时间比率
        states[6] = ((double)cpuInfo2->softirq - (double)cpuInfo1->softirq) / allTime;    // 软中断时间比率
	in = cpuInfo2->in - cpuInfo1->in;
	cs = cpuInfo2->cs - cpuInfo1->cs;
    }

    
    delete cpuInfo1;
    delete cpuInfo2;
    return cpuRate;
}

/*
*   获取CPU信息:
*   CPU_INTERRUPT, CPU_CONTEXT_SWITCH, CPU_USER, CPU_SYS, CPU_IDLE, CPU_WAIT_IO, CPU_SERVER_LOADAVG_5
*/
std::string cpuExec(int interval, LINT in, LINT cs) {
    double states[7] = {0};
    double cpuRate   = getCpuRate(in, cs, interval, states);    // CPU总体使用率
    /* 这些信息都是文档需要的 */
    double dUse    = states[0] + states[1];                // 用户态CPU使用率
    double dSys    = states[2] + states[5] + states[6];    // 系统态CPU使用率
    double dIdl    = states[3];                            // idle态CPU使用率
    double dIow    = states[4];                            // IO等待态CPU使用率
    double loadAvg = getLoadAvg();                         // 5分钟平均负载
    char retBuf[256];
    in /= (interval / 1000);
    cs /= (interval / 1000);
    
    snprintf(retBuf, sizeof(retBuf), "CPU_INTERRUPT@%lld CPU_CONTEXT_SWITCH@%lld CPU_USER@%lf CPU_SYS@%lf CPU_IDLE@%lf CPU_WAIT_IO@%lf CPU_SERVER_LOADAVG_5@%lf", in, cs, dUse*100, dSys*100, dIdl*100, dIow*100, loadAvg);
    std::string retStr = std::string(retBuf);
    // std::cout << retStr << std::endl;
    return retStr;
}



// 进程调度
std::string procsExec(LINT procs_running, LINT procs_blocked, LINT procs_swapped) {
    std::string procsStr = "procs_running@" + intToString(procs_running) + " " + "procs_blocked@" + intToString(procs_blocked) + " " + "procs_swapped@" + intToString(procs_swapped);
    return procsStr;
}


// I/O
std::string ioExec(LINT block_in, LINT block_out) {
    std::string ret = "";
    char buf[2048];
    memset(buf, '\0', sizeof(buf));
    executeCMD("iostat -xk 9 2", buf, sizeof(buf));
    std::vector<std::string> retVec;
    SplitString(std::string(buf), retVec, "\n");
    size_t devicePos = 0;   // 记录'Device:'出现的次数 (过滤掉第一个, 取第二个)
    for(size_t i  = 0; i < retVec.size(); ++i) {
        // std::cout << i << "  :  " << retVec[i] << std::endl;
        if(retVec[i].find("Device:") != std::string::npos) {
            if( devicePos != 0) {  // 第二次出现的位置
                devicePos = i;                
                break;
            }else{  // 第一次出现的位置
                devicePos = i;
            }
        }
    }
    // std::cout << "__________________________" << std::endl;
    // std::cout << "2nd occur pos is : " << devicePos << std::endl;
    ++devicePos;
    

    // 提取
    double rrqms   = 0; // 每秒读请求合并次数(次)
    double wrqms   = 0; // 每秒写请求合并次数(次)
    double rs      = 0; // 每秒读次数(次)
    double ws      = 0; // 每秒写次数(次)
    double rkb     = 0; // 每秒读数据量(kb)
    double wkb     = 0; // 每秒写数据量(kb)
    double avgrqsz = 0; // 平均每次I/O操作数量(扇区数)
    double avgqusz = 0; // 平均等待处理的I/O请求队列长度
    double await   = 0; // 平均每次I/O请求等待时间(ms)
    double svctm   = 0; // 平均每次I/O请求处理时间(ms)
    double utill   = 0; // I/O操作的时间比率(I/O队列非空的时间比率)

    /* 返回值 */
    for(size_t i = devicePos; i < retVec.size(); ++i) {
        if(retVec[i].empty()) {
            break;
        }
        // std::cout << i << " : " << retVec[i] << std::endl;
        std::vector<double> valVec = digDigit(retVec[i]);   // 取出这一行中的所有数值
        /* DEBUG */
        // for(int j = 0; j < valVec.size(); ++j) {
        //     std::cout << "[" << j << "]" << std::endl;
        //     std::cout << valVec[j] << std::endl;
        // }
        rrqms   += valVec[0];
        wrqms   += valVec[1];
        rs      += valVec[2];
        ws      += valVec[3];
        rkb     += valVec[4];
        wkb     += valVec[5];
        avgrqsz += valVec[6];
        avgqusz += valVec[7];
        await   += valVec[8];
        svctm   += valVec[9];
        utill   += valVec[10];
    }
    
    ret += ("IO_DISK_TOTAL_READ_KB@")     + doubleToString(rkb)     + " ";  // 每秒从设备读的量
    ret += ("IO_DISK_TOTAL_WRITE_KB@")    + doubleToString(wkb)     + " ";  // 每秒向设备写的量
    ret += ("IO_DISK_TOTAL_IO_UTIL@")     + doubleToString(utill)   + " ";  // 每秒内IO操作时长平均占用比例
    ret += ("IO_DISK_TOTAL_AVG_QUEUE@")   + doubleToString(avgqusz) + " ";  // 读写请求队列平均长度
    ret += ("IO_DISK_TOTAL_AVG_SERVICE@") + doubleToString(svctm)   + " ";  // 读写请求平均服务时长
    ret += ("IO_DISK_TOTAL_AVG_WAIT@")    + doubleToString(await)   + " ";  // 读写请求平均等待时长
    ret += ("IO_DISK_TOTAL_READ_MERGE@")  + doubleToString(rrqms)   + " ";  // 每秒读操作合并次数
    ret += ("IO_DISK_TOTAL_WRITE_MERGE@") + doubleToString(wrqms)   + " ";  // 每秒写操作合并次数
    ret += ("IO_DISK_TOTAL_WRITE_REQ@")   + doubleToString(ws)      + " ";  // 每秒发送到设备的写请求次数
    ret += ("IO_DISK_TOTAL_READ_REQ@")    + doubleToString(rs)      + " ";  // 每秒发送到设备的读请求次数
    return ret;
}

// 网卡
// 读取当前网卡对应的： 1、接收bps  2、接收包数目  3、发送bps  4、发送包数目, 并存入数组
void getIfconfigLine(char* buf, std::vector<LINT>& retVec) {
    for(int i = 0; i < 4; ++i) {
        retVec.push_back(0);
    }
    int count = 0;
    for(char* p = strtok(buf, " \t\r\n"); p; p = strtok(NULL, " \t\r\n")) {
        ++count;
        char valStr[20];
        strcpy(valStr, p);
        if(1 == count) {
            retVec[0] += atol(valStr);
        }
        if(2 == count) {
            retVec[1] += atol(valStr);
        }
        if(9 == count) {
            retVec[2] += atol(valStr);
        }
        if(10 == count) {
            retVec[3] += atol(valStr);
        }
    }
}

/*
*    Net 包相关的采集项
*    @return
*    0:建立的TCP连接数   1:发送TCP请求数目   2:接收TCP请求数目
*    3:socket链接句柄数  4:处于wait的TCP链接
*/
std::vector<std::string> getPackageCount() {
    FILE* fp = fopen("/proc/net/snmp", "r");    
    char buf[1024];
    fgets(buf, sizeof(buf) - 1, fp);
    while(NULL == strstr(buf, "Tcp")) {
        fgets(buf, sizeof(buf) - 1, fp);
    }
    fgets(buf, sizeof(buf) - 1, fp);
    // std::cout << buf << std::endl;
    std::vector<std::string> strVec;  // 从文件中读取
    SplitString(std::string(buf), strVec, " ");
    // printVector(strVec);
    
    std::string CurrEstab  = strVec[9];
    std::string EstbHost   = strVec[6];
    std::string EstbRemote = strVec[7];
   //  std::string AvtiveTcp  = EstbHost + EstbRemote;

    
    
    std::vector<std::string> retVec;
    retVec.push_back(CurrEstab);
    retVec.push_back(EstbHost);
    retVec.push_back(EstbRemote);

    fclose(fp);
    
    fp = fopen("/proc/net/sockstat", "r");
    fgets(buf, sizeof(buf) - 1, fp);
    while(NULL == strstr(buf, "sockets")) {
        fgets(buf, sizeof(buf) - 1, fp);
        if(feof(fp)) {
	    break;
	}
        usleep(100);
    }
    int sockUsed = 0;
    char tmp[128];
    sscanf(buf, "%s %s %d", tmp, tmp, &sockUsed);
    retVec.push_back(intToString(sockUsed));


    fseek(fp, 0, SEEK_SET);
    fgets(buf, sizeof(buf) - 1, fp);
    while(NULL == strstr(buf, "TCP")) {
        fgets(buf, sizeof(buf) - 1, fp);
        if(feof(fp)) {
	    break;
	}
        usleep(100);
    }
    int tcpTw = 0;
    sscanf(buf, "%s %s %s %s %s %s %d %s %s %s %s", tmp, tmp, tmp, tmp, tmp, tmp, &tcpTw, tmp, tmp, tmp, tmp);
    retVec.push_back(intToString(tcpTw));

    fclose(fp);
    return retVec;
}

std::string getIfconfig(int gapTime) {
    getPackageCount();
    int fd = open("/proc/net/dev", O_RDONLY | O_EXCL);
    if(-1 == fd) {
        printf("/proc/net/dev not exists!\n");
        return "";
    }
    char buf[1024 * 2];
    lseek(fd, 0, SEEK_SET); // 文件指针指向开头
    int nBytes = read(fd, buf, sizeof(buf)-1);
    if (-1 == nBytes) {  
        perror("read error");  
        close(fd);  
        return "";  
    }

    // 第一次采集
    char* pDev = strstr(buf, ":");
    std::vector<LINT> totalVec1;
    for(size_t i = 0; i < 4; ++i) {
	totalVec1.push_back(0);
    }
    while(pDev != NULL) {
        ++pDev;
        char tmpbuf[2048];
        strcpy(tmpbuf, pDev);
        getIfconfigLine(tmpbuf, totalVec1);
        pDev = strstr(pDev, ":");
    }

    // 获取包相关
    std::vector<std::string> pkgVec1 = getPackageCount();

    sleep(gapTime);
    // 需要重新打开一遍文件
    // 第二次采集
    close(fd);
    fd = open("/proc/net/dev", O_RDONLY | O_EXCL);
    if(-1 == fd) {
    std::vector<std::string> pkgVec1 = getPackageCount();
        printf("/proc/net/dev not exists!\n");
        return "";
    }
    memset(buf, 0, sizeof(buf));
    lseek(fd, 0, SEEK_SET);
    nBytes = read(fd, buf, sizeof(buf)-1);
    if (-1 == nBytes) {  
        perror("read error");  
        close(fd);  
        return "";  
    }
    pDev = strstr(buf, ":"); 
    std::vector<LINT> totalVec2;
    for(size_t i = 0; i < 4; ++i) {
	totalVec2.push_back(0);
    }
    while(pDev != NULL) {
        ++pDev;
        char tmpbuf[2048];
        strcpy(tmpbuf, pDev);
        getIfconfigLine(tmpbuf, totalVec2);
        pDev = strstr(pDev, ":");
    }
    std::vector<std::string> pkgVec2 = getPackageCount();

    // 计算速率
    std::string netInfo = "NET_TOTAL_IN_BITPS@"  + intToString(8 / gapTime * (totalVec2[0] - totalVec1[0])) + " "; 
    netInfo += "NET_TOTAL_IN_PKG@"    + intToString((totalVec2[1] - totalVec1[1]) / gapTime) + " "; 
    netInfo += "NET_TOTAL_OUT_BITPS@" + intToString(8 / gapTime * (totalVec2[2] - totalVec1[2])) + " "; 
    netInfo += "NET_TOTAL_OUT_PKG@"   + intToString((totalVec2[3] - totalVec1[3]) / gapTime) + " "; 
    netInfo += "NET_TCP_CURR_ESTAB@"   + intToString(( atoi(pkgVec1[0].c_str()) + atoi(pkgVec2[0].c_str()) ) / 2) + " ";	// TPC连接数，这一项取平均值 

    LINT sndTcp = atol(pkgVec2[1].c_str()) - atol(pkgVec1[1].c_str());	// TPC发送连接数
    LINT rcvTcp = atol(pkgVec2[2].c_str()) - atol(pkgVec1[2].c_str()); 	// TPC接收链接数
    LINT actTcp = (sndTcp + rcvTcp) / gapTime;				// 活跃的TCP连接数
    netInfo += "NET_TCP_ACTIVE_OPENS@"   + intToString(actTcp) + " ";	// TPC活跃连接数
    netInfo += "NET_TOTAL_SOCKETS_USED@" + intToString(( atoi(pkgVec1[3].c_str()) + atoi(pkgVec2[3].c_str()) ) / 2) + " ";	// socket链接句柄总数 
    netInfo += "NET_TCP_TIME_WAIT@" + intToString(( atoi(pkgVec1[4].c_str()) + atoi(pkgVec2[4].c_str()) ) / 2) + " ";	        // 处于TIME_WAIT的TCP连接数
    close(fd);
    return netInfo;
}


/*
*   @param1  采样间隔
*/
std::string netInfos(int gapTime) {
    std::string netInfos = getIfconfig(gapTime);
    return netInfos;
}
