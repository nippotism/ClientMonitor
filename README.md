
## Langkah Instalasi

1. Clone Repository

    ```bash
    git clone https://github.com/nippotism/ClientMonitor
    cd ClientMonitor
    ```
    

2. Install Guzzle

    ```bash
    composer require guzzlehttp/guzzle:7.7 --with-all-dependencies
    ```
    
3. Pindah BackupPost,QueryPost, dan CpuPost ke
    ```bash
   {your-path}/app/Console/Commands
    ```
4. Setup Commands

   untuk semua commands, sesuaikan nama dan server rumah sakit.
   ```bash
   $body['name'] = 'NAMA RUMAH SAKIT'; #isi dengan nama rumah sakit
   $body['server'] = 'NAMA SERVER'; #isi dengan nama server
   ```
   isi juga api password
   ```bash
   'api-password' => 'PASSWORD API' #isi dengan password api
   ```
    

## Setup Task Scheduler

1. *Setup File .bat*

    Sesuaikan file .bat untuk task scheduler sesuai dengan path di komputer Anda.

    schedulerCpuQueries.bat:

    ```bash
    @echo off
    cd {Your Path}\sim_rs
    php artisan cpu:post
    php artisan queries:post
    ```
    

    schedulerBackup.bat:

    Sesuaikan pathnya dengan path di komputer client, lakukan juga untuk backup.bat.

2. Import Task Scheduler di Windows

    - Buka Task Scheduler di Windows.
    - Import task XML yang sudah disediakan (CPU Util & Queries.xml dan Backup Monitoring.xml).
      ![WhatsApp Image 2024-08-05 at 11 08 01_36aa7736](https://github.com/user-attachments/assets/662d20fd-a7f3-4655-936e-e247d5166b54)
    - Ubah object name dengan 'Administrator'
      ![image](https://github.com/user-attachments/assets/9bf816d5-63e5-4e1b-abfa-0a68b1f8bd4e)
    - Sesuaikan path untuk actionnya yaitu file .bat yang sudah disesuaikan di atas.
      ![WhatsApp Image 2024-08-05 at 11 10 15_db169d88](https://github.com/user-attachments/assets/e9937927-b012-4639-be89-62c90a4cfed9)
      

