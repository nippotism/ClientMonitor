<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class BackupPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:post';

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
        $quer = "SELECT 
                A.[Server],  
                A.database_name,
                A.last_db_backup_date,  
                B.backup_start_date,  
                B.backup_size,  
                B.physical_device_name,   
                B.backupset_name
                FROM 
                ( 
                SELECT   
                    CONVERT(CHAR(100), SERVERPROPERTY('Servername')) AS Server, 
                    msdb.dbo.backupset.database_name,  
                    MAX(msdb.dbo.backupset.backup_finish_date) AS last_db_backup_date 
                FROM 
                    msdb.dbo.backupmediafamily  
                    INNER JOIN msdb.dbo.backupset ON msdb.dbo.backupmediafamily.media_set_id = msdb.dbo.backupset.media_set_id  
                WHERE 
                    msdb..backupset.type = 'D' 
                GROUP BY 
                    msdb.dbo.backupset.database_name  
                ) AS A 
                LEFT JOIN  
                ( 
                SELECT   
                    CONVERT(CHAR(100), SERVERPROPERTY('Servername')) AS Server, 
                    msdb.dbo.backupset.database_name,  
                    msdb.dbo.backupset.backup_start_date,  
                    msdb.dbo.backupset.backup_finish_date, 
                    msdb.dbo.backupset.expiration_date, 
                    msdb.dbo.backupset.backup_size,  
                    msdb.dbo.backupmediafamily.logical_device_name,  
                    msdb.dbo.backupmediafamily.physical_device_name,   
                    msdb.dbo.backupset.name AS backupset_name, 
                    msdb.dbo.backupset.description 
                FROM 
                    msdb.dbo.backupmediafamily  
                    INNER JOIN msdb.dbo.backupset ON msdb.dbo.backupmediafamily.media_set_id = msdb.dbo.backupset.media_set_id  
                WHERE 
                    msdb..backupset.type = 'D' 
                ) AS B 
                ON A.[server] = B.[server] AND A.[database_name] = B.[database_name] AND A.[last_db_backup_date] = B.[backup_finish_date] 
                ORDER BY  
                A.database_name ";
        $body= DB::select($quer);
        $body['name'] = 'NAMA RUMAH SAKIT'; #isi dengan nama rumah sakit
        $body['server'] = 'NAMA SERVER'; #isi dengan nama server
        $body = json_encode($body);
        $headers = [
            'Content-Type' => 'application/json',
            'api-password' => 'PASSWORD API' #isi dengan password api
          ];
        $client = new Client();
        $request = new Request('POST', 'http://elimspro.co.id:5656/prolimslog/api/backup-info', $headers, $body);
        $res = $client->sendAsync($request)->wait();
        Log::info($res->getBody()->getContents());
    }
}
