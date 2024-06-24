<?php 
if (! function_exists('moneyFormat')) {
        
    /**
     * moneyFormat
     *
     * @param  mixed $str
     * @return void
     */
    function moneyFormat($str) {
        return 'Rp. ' . number_format($str, '0', '', '.');;
    }
}

// lalu tambahkan ke composer.json
// "autoload": {
//   ......
//   "files": [
//      "app/Helpers/helpers.php"
//   ]
// },

// jalankan perintah composer dump-autoload

?>