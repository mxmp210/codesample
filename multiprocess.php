<?php

function get_cpu_count() {
    return (int) ((PHP_OS_FAMILY == 'Windows')?(getenv("NUMBER_OF_PROCESSORS")+0):substr_count(file_get_contents("/proc/cpuinfo"),"processor"));
 }