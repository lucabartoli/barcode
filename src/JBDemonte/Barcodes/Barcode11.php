<?php
namespace JBDemonte\Barcodes;

  class Barcode11 {
    static private $encoding = array(
                '101011', '1101011', '1001011', '1100101',
                '1011011', '1101101', '1001101', '1010011',
                '1101001', '110101', '101101');

    static public function getDigit($code){
      if (preg_match('`[^0-9\-]`', $code)) return '';
      $result = '';
      $intercharacter = '0';
      
      // start
      $result = '1011001' . $intercharacter;
      
      // digits
      $len = strlen($code);
      for($i=0; $i<$len; $i++){
        $index = $code[$i] == '-' ? 10 : intval($code[$i]);
        $result .= self::$encoding[ $index ] . $intercharacter;
      }
      
      // checksum
      $weightC    = 0;
      $weightSumC = 0;
      $weightK    = 1; // start at 1 because the right-most character is 'C' checksum
      $weightSumK = 0;
      for($i=$len-1; $i>-1; $i--){
        $weightC = $weightC == 10 ? 1 : $weightC + 1;
        $weightK = $weightK == 10 ? 1 : $weightK + 1;
        
        $index = $code[$i] == '-' ? 10 : intval($code[$i]);
        
        $weightSumC += $weightC * $index;
        $weightSumK += $weightK * $index;
      }
      
      $c = $weightSumC % 11;
      $weightSumK += $c;
      $k = $weightSumK % 11;
      
      $result .= self::$encoding[$c] . $intercharacter;
      
      if ($len >= 10){
        $result .= self::$encoding[$k] . $intercharacter;
      }
      
      // stop
      $result  .= '1011001';
      
      return($result);
    }   
  }
  