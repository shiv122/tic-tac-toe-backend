# Tic Tac Toe Api

This is backend for tic tac tow api made Laravel and reverb

## Prerequisites

Make sure you have the following installed on your machine:

- php 8.2 ( or later)
- Mysql or mariadb or you can use sqlite (change env accordingly)

## Getting Started

1. **Clone the repository:**

   git clone https://github.com/shiv122/tic-tac-toe-backend.git
   cd tic-tac-toe-backend

2. **Install dependencies:**

   `composer install`

3. **Run Migration:**

   `php artisan migrate`

4. **Set up environment variables:**

   Rename `.env.example` to `.env`

5. **Setup Reverb:**

   `php artisan install:broadcasting`
   and then run it
   `php artisan reverb:start --port=6001`

6. **Setup Redis Queue (optional):**

   You will only need this if you are using redis as queue driver `QUEUE_CONNECTION=redis`  
   change this to `sync` if you dont want a to setup

   if you are using queue run
   `php artisan queue:work --sleep=0`

7. **Run laravel app:**

   `php artisan:serve`

## Configuration

### .env File

Make sure you have same value in front end. if you change it , put same value in
`plugins\laravel-echo.client.js` in frontend

```bash
REVERB_APP_ID=610705
REVERB_APP_KEY=yl9nwwgxhaxgobgbej11
REVERB_APP_SECRET=sroopccitwua5a0agsa3
REVERB_HOST="lara-api.ddev.site"
REVERB_PORT=6001
REVERB_SCHEME=http`
```

## Contributing

Feel free to contribute to this project by submitting issues or pull requests.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any questions or feedback, please reach out to [rootshiv.dev](https://rootshiv.dev).

Happy coding!
