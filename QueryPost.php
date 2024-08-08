<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class QueryPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queries:post';

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
        $quer = "DECLARE @lastNmin INT;
                SET @lastNmin = 10;

                SELECT TOP 10
                    CONVERT(CHAR(100), SERVERPROPERTY('Servername')) AS Server,
                    dest.TEXT AS [Query],
                    SUM(deqs.execution_count) AS [Count],
                    MAX(deqs.last_execution_time) AS [Time]
                FROM sys.dm_exec_query_stats AS deqs
                CROSS APPLY sys.dm_exec_sql_text(deqs.sql_handle) AS dest
                CROSS APPLY sys.dm_exec_plan_attributes(deqs.plan_handle) AS epa
                WHERE epa.attribute = 'dbid'
                AND DB_NAME(CONVERT(int, epa.value)) NOT IN ('master', 'tempdb', 'model', 'msdb')
                AND deqs.last_execution_time >= DATEADD(MINUTE, -@lastNmin, GETDATE())
                GROUP BY dest.TEXT, DB_NAME(CONVERT(int, epa.value))
                ORDER BY SUM(deqs.execution_count) DESC;";
        $body= DB::select($quer);
        $body['name'] = 'NAMA RUMAH SAKIT'; #isi dengan nama rumah sakit
        $body['server'] = 'NAMA SERVER'; #isi dengan nama server
        $body = json_encode($body);
        $headers = [
            'Content-Type' => 'application/json',
            'api-password' => 'PASSWORD API' #isi dengan password api
          ];
        $client = new Client();
        $request = new Request('POST', 'http://elimspro.co.id:5656/prolimslog/api/queries', $headers, $body);
        $res = $client->sendAsync($request)->wait();
        Log::info($res->getBody()->getContents());
    }
}
