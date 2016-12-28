# Urly - Minimum viable URL shortener
Do you want to encode URLs like tinyurl.com does?  
This is helper tool for you.

### Features
Urly uses PDO interface to store URLs. Just refer to your existing connection.

Optionally you can specify table name, key encode base and XOR mask.

Access methods are quite obvious: `set($url)` and `get($key)`.

### Install

To install with composer:

```sh
composer require artoodetoo/urly
```

Required table structure:

```sql
CREATE TABLE `urly` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
)
```

### Basic Usage

Save URL and het encoded key:
```php
$db = new \PDO(
    'mysql:dbname=homestead;host=127.0.0.1;charset=utf8',
    'homestead', 
    'secret'
);

// Set 62 base to get alfa-numeric key in both cases and 
// some magic number to make key sequence be less predictable
$shortener = new \R2\Utility\Urly($db, 'my_urly', 62, 990749);

$key = $shortener->set('http://localhost/test.txt');
echo 'http://go.to/'.$key."\n"; // Something like 'http://go.to/49Jz'
```

Get URL by key:
```php
echo $shortener->get('49Jz'); // Saved URL or empty string if not found
```

### License

The Urly is open-source software, licensed under the [MIT license](http://opensource.org/licenses/MIT)
