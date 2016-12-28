<?php

/*
 * Minimum viable URL shortener.
 * You can set arbitrary key base (up to 62):
 * - 10 is simple decimal,
 * - 16 is hexadecimal lower case
 * - 32 is alfanumeric lower case, 
 * - 62 is alfanumeric both cases
 * To get sequence less predictable, you can specify XOR mask for key.
 *
 * Used StackOverflow recipes:
 * http://stackoverflow.com/questions/959957/php-short-hash-like-url-shortening-websites 
 * http://stackoverflow.com/questions/4964197/converting-a-number-base-10-to-base-62-a-za-z0-9
 *
 * Use this SQL to create URL table (table name is up to you):
 *
 * CREATE TABLE `urly` (
 *   `id` int(10) NOT NULL AUTO_INCREMENT,
 *   `url` varchar(1000) NOT NULL,
 *   PRIMARY KEY (`id`)
 * )
 *
 */

namespace R2\Utility;

class Urly
{
    private $db;
    private $table;
    private $base;
    private $xor;

    const ABC = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //                  10^                       32^                       62^

    /**
     * Constructor.
     * @param \PDO   $db    PDO instance 
     * @param string $table table name
     * @param string $base  base for short key
     */
    public function __construct(\PDO $db, $table = 'urly', $base = 62, $xor = 0)
    {
        $this->db = $db;
        $this->table = $table;
        $this->base = $base;
        $this->xor = $xor;
    }

    /**
     * Save URL to database and get short key
     * @param string $url
     * @return string
     */
    public function set($url)
    {
        $this->db
            ->prepare("INSERT INTO `{$this->table}`(`url`) VALUES(?)")
            ->execute([$url]);
        $id = $this->db->lastInsertId();
        return $this->encode($id ^ $this->xor);
    }

    /**
     * Retrieve URL from database by its short key
     * @param string $key Short key
     * @return string URL or empty string if key not found
     */
    public function get($key)
    {
        $id = $this->decode($key) ^ $this->xor;
        $sth = $this->db->prepare(
            "SELECT `url` FROM `{$this->table}` WHERE `id` = ?");
        $sth->execute([$id]);
        $result = $sth->fetch(\PDO::FETCH_ASSOC);
        return $result ? $result['url'] : '';
    }

    private function encode($num)
    {
        if ($this->base <= 32) {
            return base_convert($num, 10, $this->base);
        }
        $r = $num % $this->base ;
        $res = self::ABC[$r];
        $q = floor($num / $this->base);
        while ($q) {
            $r = $q % $this->base;
            $q = floor($q / $this->base);
            $res = self::ABC[$r].$res;
        }
        return $res;
    }

    private function decode($num)
    {
        if ($this->base <= 32) {
            return base_convert($num, $this->base, 10);
        }
        $limit = strlen($num);
        $res = strpos(self::ABC, $num[0]);
        for($i = 1; $i < $limit; $i++) {
            $res = $this->base * $res + strpos(self::ABC, $num[$i]);
        }
        return $res;
    }
}
