# Yet Another Tieba Sign

## Requirement

- PHP 7+
- MySQL
- Apache or Nginx
- NodeJS
- Redis

## Architecture

- Laravel
- Vue

## Start Development

- Install PHP composer
- Install Node and npm
- Run `composer install` and `npm install`
- Modify `.env` to configure the database
- `php artisan migrate`
- `php artisan serve` and `npm run watch`
- Add a test user: `php artisan user:add {username} {password}`
- Use `php artisan queue:listen` to start signing job
- Start developing!

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
