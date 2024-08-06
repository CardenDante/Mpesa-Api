
# Mpesa API Testing

## Overview

This project is designed for testing purposes to facilitate the integration and validation of the Mpesa API. It provides a comprehensive setup to perform and test Mpesa STK push transactions.

## Features

- Fetch and manage Mpesa access tokens
- Format phone numbers to the correct Mpesa format
- Initiate Mpesa STK push transactions
- Comprehensive logging and error handling

## Getting Started

### Prerequisites

Ensure you have the following installed on your development machine:

- PHP >= 7.4

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/CardenDante/Mpesa-Api.git
   cd Mpesa-Api
   ```

2. **Configuration**

   Create a `config.php` file in the project root and fill in your Mpesa API credentials and other necessary configuration:

   ```php
   <?php
   define('MPESA_SHORTCODE', 'your_shortcode');
   define('MPESA_CONSUMER_KEY', 'your_consumer_key');
   define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
   define('MPESA_PASSKEY', 'your_passkey');
   define('MPESA_CALLBACK_URL', 'https://yourcallbackurl.com');
   define('MPESA_ENV', 'sandbox'); // or 'live'
   ?>
   ```

### Usage

1. **Run the PHP script**

   You can run the provided PHP script on a server or a local development environment. Make sure the server is configured to execute PHP scripts.

2. **Testing the STK Push**

   To test the STK push functionality, send a POST request to the script with the necessary parameters (`amount` and `phone`). Here's an example using `curl`:

   ```bash
   curl -X POST -d "amount=100&phone=254712345678" http://yourserverpath/your_php_script.php
   php -S localhost:8000
   ```


## Collaboration

For collaboration:

1. **Fork the repository**

   Click the "Fork" button on the top right of the repository page.

2. **Make your changes**

   Clone your forked repository to your local machine and make your changes.

   ```bash
   git clone https://github.com/your-username/Mpesa-Api.git
   cd Mpesa-Api
   ```

3. **Push your changes**

   Commit your changes and push to your forked repository.

   ```bash
   git add .
   git commit -m "Your commit message"
   git push origin main
   ```

4. **Submit a pull request**

   Go to the original repository on GitHub and submit a pull request with a description of your changes.

## License

This project is open-source and available under the [MIT License](LICENSE).

---

Feel free to test the project and use it according to your needs. Your contributions are welcome and appreciated!

## Contact

For any queries or issues, feel free to open an issue or contact the maintainer.

