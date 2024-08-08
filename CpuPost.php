<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class CpuPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cpu:post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //

        $cpuquery = "WITH SpaceUsage AS (
                        SELECT 
                            SUM(CASE WHEN mf.[type] = 0 THEN mf.size * 8.0 / 1024 END) AS data_used_size,
                            SUM(CASE WHEN mf.[type] = 1 THEN mf.size * 8.0 / 1024 END) AS log_used_size
                        FROM sys.master_files mf
                        JOIN sys.databases d ON d.database_id = mf.database_id
                        WHERE d.[state] = 0
                    ),
                    DiskUsage AS (
                        SELECT 
                            SUM(CAST(mf.size * 8.0 / 1024 AS DECIMAL(18,2))) AS total_size,
                            SUM(CASE WHEN mf.[type] = 0 THEN CAST(mf.size * 8.0 / 1024 AS DECIMAL(18,2)) END) AS data_size,
                            (SELECT SUM(data_used_size) FROM SpaceUsage) AS used_data_size
                        FROM sys.master_files mf
                        JOIN sys.databases d ON d.database_id = mf.database_id
                    ),
                    RAMUsage AS (
                        SELECT
                            (total_physical_memory_kb / 1024) AS total_memory_mb,
                            ((total_physical_memory_kb - available_physical_memory_kb) / 1024) AS memory_in_use_mb,
                            (SELECT physical_memory_in_use_kb / 1024 FROM sys.dm_os_process_memory) AS sql_memory_mb
                        FROM sys.dm_os_sys_memory
                    ),
                    CPUUsage AS (
                        SELECT 
                            (100 - x.value('(./Record/SchedulerMonitorEvent/SystemHealth/SystemIdle/text())[1]', 'TINYINT')) AS cpu_total,
                            (cpu_sql / cpu_base * 100) AS cpu_sql
                        FROM (
                            SELECT TOP(1) [timestamp], x = CONVERT(XML, record)
                            FROM sys.dm_os_ring_buffers
                            WHERE ring_buffer_type = N'RING_BUFFER_SCHEDULER_MONITOR'
                            AND record LIKE '%<SystemHealth>%'
                            ORDER BY [timestamp] DESC
                        ) r
                        CROSS APPLY (
                            SELECT 
                                MAX(CASE WHEN counter_name = 'CPU usage %' THEN cntr_value END) AS cpu_sql,
                                MAX(CASE WHEN counter_name = 'CPU usage % base' THEN cntr_value END) AS cpu_base
                            FROM sys.dm_os_performance_counters
                            WHERE counter_name IN ('CPU usage %', 'CPU usage % base')
                            AND instance_name = 'default'
                        ) pc
                    )
                    SELECT
                        'server' AS server,
                        cpu.cpu_total AS cpu_utilization,
                        ISNULL(cpu.cpu_sql, 0) AS cpu_sql_util,
                        ram.total_memory_mb,
                        ram.memory_in_use_mb,
                        ram.sql_memory_mb,
                        disk.total_size AS disk_size,
                        disk.data_size AS data_size,
                        disk.used_data_size
                    FROM CPUUsage cpu
                    CROSS JOIN RAMUsage ram
                    CROSS JOIN DiskUsage disk;
                    ";


        $body= DB::select($cpuquery);
        $body[0]->name = 'NAMA RUMAH SAKIT'; #isi dengan nama rumah sakit
        $body = trim(json_encode($body), '[]');
        $headers = [
            'Content-Type' => 'application/json',
            'api-password' => 'PASSWORD API' #isi dengan password api
          ];
        $client = new Client();
        $request = new Request('POST', 'http://elimspro.co.id:5656/prolimslog/api/cpu', $headers, $body);
        // dd($request);
        $res = $client->sendAsync($request)->wait();
        Log::info($res->getBody()->getContents());

    }
}
