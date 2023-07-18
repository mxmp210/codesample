# Code Sample - ReactPhp - Simple API Server
Sample code for people who wish to know my coding & doc style for microservices, RESTful APIs & usage of High Performance php frameworks.

*Note : This is only fun project repository to fiddle with Async Programming done in couple of hours to showcase dynamic programming. Actural architecture of realworld applications can get much complex than sample CRUD code.*

This codebase represents sample code of a microservice based on [ReactPhP](https://reactphp.org/) but can be implemented with any framework ( Symfony, Laravel, Slim, Lumen, etc. ) having an endpoint which accepts a POST request with the following parameters:

## Customer
---
Customer Object storing basic details
```MSON
id: 4f960455-833f-426d-8e91-0a831b0218c4 (UUIDv4, required)
email (string, required)
phone (string, optional)
name (string, required)
comment (string, required, max length: 1000 characters)
created_at (timestamp, required)
updated_at (timestamp, required)
```
## Endpoints
---
This application contians basic CRUD endpoints for Customer Resource

<table>
<thead>
<tr>
<th>Resource</th>
<th>Endpoint</th>
<th>Type</th>
<th>Parameters / Body</th>
</tr>
</thead>
<tbody>
<tr>
<td rowspan="4">Customer</td>
<td rowspan="4">/api/customer</td>
<td>GET</td>
<td>id</td>
</tr>
<tr>
<td>POST</td>
<td>Customer</td>
</tr>
<tr>
<td>PUT</td>
<td>Customer</td>
</tr>
<tr>
<td>DELETE</td>
<td>id</td>
</tr>
</tbody>
</table>

## Assumptions / Behavior
---
- The endpoint returns HTTP JSON response.
- Application assumes usage of mysql as primary storage with following table structure :
    ```sql
    CREATE TABLE `codesample`.`customer1` ( `id` BINARY(16) NOT NULL COMMENT 'UUIDv4' , `name` VARCHAR(255) NOT NULL COMMENT 'name of org' , `email` VARCHAR(64) NOT NULL COMMENT '64 chars max / 40 is average' , `phone` VARCHAR(15) NULL COMMENT 'E.164 Format' , `comment` VARCHAR(1000) NOT NULL COMMENT 'Comment 1000chars max' , `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`(16)), UNIQUE `email` (`email`), INDEX `timestamps` (`created_at`, `updated_at`)) ENGINE = InnoDB;
    ```
    Note : DB Collate can be optimised but are out of scope for sample.
- Validation of input data is done using symfony validator
- Unit Tests are provided to check resource operations
- It is assumed reader has knowledge about Datbase Abstractions, ORM & Migrations, Asynchronous Programming, Singleton Pattern, Validators and Popular PHP libraries utilised in the codebase.

## Stack Utilised
---
Current example uses :
- PHP 8.1+ with ReactPhP as HTTP Server & Fast Router
- Symfony DotEnv & Validator Components
- Mysql / MariaDB with cycle/orm
- Swagger for Docs

## Setup & Installation
---
1. Check Environement & Minimum Requirements : `MySQL 8.0+ / MariaDB 10.3+, PHP 8.1+`
2. Clone Repository `git clone https://github.com/mxmp210/codesample`
3. Install Dependencies via `composer install`
4. Modify Environment file (via copying `.env` file ) : `.env.local`
5. Run Server :  `php server.php`
6. Browse API : http://localhost:8000/

Hope this helps, if you have read so far - connect with me [@mxmp210](https://github.com/mxmp210/)!

## Dev Auto Reload - loacal setup
---
Dev setup comes with [nodemon](https://github.com/remy/nodemon) setup.
Setps to getting it run on machine : 
1. install nodemon `npm install`
2. Run via : `npm start` or `nodemon server.php`

## Docker Setup
---
App can run on custom build docker image - given example for tailored environment. Ideal for production environements i.e. Docker, Docker Swarm, k3s or k8s.

Setps to getting it run on docker containers : 
1. Build docker image  `docker-compose build`
2. Run via : `docker-compose up -d`

## Benchmarks / Insights
---
Sample tests are done on old AMD 2400G 4 Core CPU with hyperthreading, 8GB system ram, mysql and redis running on same machine with DB on magnatic disk with **single thread** in performance for worker.

Benchmark assumes 10000 requests done by 100 concurrent users with Apache Benchmark `ab -n 10000 -c 100 http://127.0.0.1:8080/api/v1/customers/<uuid>`

The endpoint `/api/v1/customers` returning collection 
```
Server Software:        ReactPHP/1
Server Hostname:        127.0.0.1
Server Port:            8080

Document Path:          /api/v1/customers/
Document Length:        210 bytes

Concurrency Level:      100
Time taken for tests:   7.481 seconds
Complete requests:      10000
Failed requests:        0
Total transferred:      3390000 bytes
HTML transferred:       2100000 bytes
Requests per second:    1336.80 [#/sec] (mean)
Time per request:       74.806 [ms] (mean)
Time per request:       0.748 [ms] (mean, across all concurrent requests)
Transfer rate:          442.55 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.4      0       1
Processing:    24   74   7.9     72     113
Waiting:        1   74   8.0     72     112
Total:         24   74   7.9     72     113

Percentage of the requests served within a certain time (ms)
  50%     72
  66%     74
  75%     76
  80%     77
  90%     82
  95%     89
  98%     99
  99%    103
 100%    113 (longest request)
 ```
Api ednpoint : 
```
...

Document Path:          /api/v1/customers/1ef71769-85ca-411d-b19e-6b9cd3940e6b
Document Length:        178 bytes

Concurrency Level:      100
Time taken for tests:   7.346 seconds
Complete requests:      10000
Failed requests:        0
Total transferred:      3070000 bytes
HTML transferred:       1780000 bytes
Requests per second:    1361.36 [#/sec] (mean)
Time per request:       73.456 [ms] (mean)
Time per request:       0.735 [ms] (mean, across all concurrent requests)
Transfer rate:          408.14 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.4      0       1
Processing:    21   73   7.2     71     110
Waiting:        1   73   7.3     71     110
Total:         22   73   7.3     72     110

Percentage of the requests served within a certain time (ms)
  50%     72
  66%     74
  75%     76
  80%     76
  90%     79
  95%     85
  98%     97
  99%    102
 100%    110 (longest request)
```
Tradeoff : Indiivdual endpoint will eprform slower in moderate traffic but it would reduce alot of burdern on DB server and can get away with high traffic websites where read operations are alot more than write operations.

## Optional TODOs:
---
- User Auth
- Middlewares
- DB Seeds & Migrations
- Git CI Pipeline
- Unit Tests
- Multiprocess Server ( Currently Single Threaded, Multi Container Config in Docker)

## License and Copyright
---
MIT, see [LICENSE file](LICENSE).

## 👉🏻 [Click here to contact](https://github.com/mxmp210#connect-with-me) 👈🏻
---
