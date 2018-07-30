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
- Run `composer install`
- Modify `.env` to configure the database
- `php artisan migrate`
- Add a test user: `php artisan user:add {username} {password}`
- Configure passport: `php artisan passport:install`
- Configure a password grant client: `php artisan passport:client --password`

    Remember the `Client ID` and `Client Secret`. It's necessary in frontend configuration.

- Run the develop server: `php artisan serve`
- Run a new terminal and use `php artisan queue:listen --timeout=60000` to start signing job (consider that the default timeout 60s is not enough to sign all tiebas)
- Reference to according guide to build the frontend
- Start developing!

## Frontend
https://github.com/hebingchang/Yet-Another-Tieba-Sign-Frontend

```
npm install
npm run dev
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
