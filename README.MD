# Contest System

## Description
- This is a contest system it have users, contest, questions, answers,participations of user, ans given by user and price and leaderboard.
The system is implemented using Laravel Framework.
- there are 3 types of user,
    - admin
    - vip
    - normal

## Installation
- Clone the repository
- Run `composer install` to get the dependency installed
- Run `npm install && npm run build` to get the frontend dependencies installed
- Run `cp .env.example .env` to create a new .env file from .env.example
- Run `php artisan key:generate` to generate a new key
- Run `touch database/database.sqlite` to create sqlite db, or update the .env file with the database credentials
- Run `php artisan migrate:fresh --seed` to create the database and seed it with some data we will use sqlite for now, in seed it will create 3 users you can change the database configuration in the .env file
- Run `composer run dev` to start server

## API Endpoints
- here's the postman collection link [tentwenty.postman_collection.json](tentwenty.postman_collection.json) it consist of all the api endpoints and their request data and response.
- pass Bearar token in the header for the apis that require authentication. use `/login` or `/register` to get the token.
- added rate limit for api requests 600 per minute. you can change it in the `AppServiceProvider` file in `boot` method.
- i have not setup ratelimite for the apis individual calls, but we can do that by using `RateLimiter` facades or `throttle` middleware.
- i have used `apiResource` for the apis that require CRUD operations.

## Database Schema
- i have created 6 tables in the database
    - contests
    - questions
    - answers
    - user_answers
    - participations
    - prizes

## TODO
- i have not added contest end job and based on that result will be calculated. ( Prize )
- for now i have added result calculation ( Prize ) in end of participations, which ever user scores the highest will be the winner. and have prize.
- have indexing on the database tables. so that we can get the data faster.
- added validations most places but i feel like we can have more validations.
- test cases are not written due to time limit
- also we can handle roles and permissions in a better way. may be with using spatie package.

## Assumptions
- i have assumed that the contest will have only one winner. and one Prize only.
- i have assumed that the user will have only one role at a time.
- based on user role the user will have access to the contest.
