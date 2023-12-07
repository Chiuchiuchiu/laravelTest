# 操作步驟
* 1.安裝核心文件 
    ```composer update --ignore-platform-req=ext-fileinfo```
* 2.添加.env文件并生成key
    ```php artisan key:generate```
* 3.執行腳本
    ```php artisan app:evalCommand```
* 4.安裝測試模塊
    ```composer global require phpunit/phpunit```  
* 5.執行測試
     ```phpunit```  
