<!-- <p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). -->

## Multi-Tenant Paystack Gateway
this app stands as a centrall of middle man between the several applications and paystack gateway

## Basic setup
after using composer to install the necessary plugins and running migration the **php artisan serve** need to run to start the server then **php artisan queue:work** need to run to process the webhook

## Configurations
first the several applications must have to register with the central gateway by providing
the following property
1. name  <!-- application name -->
2. paystack_public_key
3. paystack_private_key
4. callback_url
5. webhook_url

to the gateway link  **base_url/api/application**
and then a response will be sent back which contains the property **id** which is the application id, the datas are stored in the **applications** table which is used to fetch the paystack public key and private key later on during payment verification and sending webhook events to the individual applications webhooks url 

## Making payment through the central gateway
to make payment through the central gateway the application have to 

# first
send the following properties (app_id,email,amount,currency,reference) to the **base_url/api/initializePayment** to initialize payment and a response that contains the paystack payment link for payment will be given to you and the name of the url property is **authorization_url**. copy the authorization url and paste it on your browser and it will take you  to where to make payment

note 
app_id is the application id given to you by the centrall gateway while the 
reference property must be unique  also during payment intialization the properties will be saved to the **transactions** table which will be updated to either successfull or failed during making the actuall payment



# secondly
after making payment it will redirect you to your callback_url you provided with a status query attached to it

# thirdly
if payment is successfull a webhook event will be sent to the webhook url you provided during registering your application to the central gateway and also the laravel **php artisan queue:work** has to be run in other to run the queue that will validate the webhook events from paystack and then send the events to the individual application webhook url

# some other things to do
the (centrall payment app) webhook listener url should be the url that is registered at the  individual Application paystack webhook url field in paystack. this will make the centrall payment app recieve events from the paystack system, validate the request and then do some further things on the backend and then redirect the data to the individual application that is meant to recieve the event.






