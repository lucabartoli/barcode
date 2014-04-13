<?php
namespace JBDemonte\Barcodes;


 class Barcode128 {
    static private $encoding = array(
              '11011001100', '11001101100', '11001100110', '10010011000',
              '10010001100', '10001001100', '10011001000', '10011000100',
              '10001100100', '11001001000', '11001000100', '11000100100',
              '10110011100', '10011011100', '10011001110', '10111001100',
              '10011101100', '10011100110', '11001110010', '11001011100',
              '11001001110', '11011100100', '11001110100', '11101101110',
              '11101001100', '11100101100', '11100100110', '11101100100',
              '11100110100', '11100110010', '11011011000', '11011000110',
              '11000110110', '10100011000', '10001011000', '10001000110',
              '10110001000', '10001101000', '10001100010', '11010001000',
              '11000101000', '11000100010', '10110111000', '10110001110',
              '10001101110', '10111011000', '10111000110', '10001110110',
              '11101110110', '11010001110', '11000101110', '11011101000',
              '11011100010', '11011101110', '11101011000', '11101000110',
              '11100010110', '11101101000', '11101100010', '11100011010',
              '11101111010', '11001000010', '11110001010', '10100110000',
              '10100001100', '10010110000', '10010000110', '10000101100',
              '10000100110', '10110010000', '10110000100', '10011010000',
              '10011000010', '10000110100', '10000110010', '11000010010',
              '11001010000', '11110111010', '11000010100', '10001111010',
              '10100111100', '10010111100', '10010011110', '10111100100',
              '10011110100', '10011110010', '11110100100', '11110010100',
              '11110010010', '11011011110', '11011110110', '11110110110',
              '10101111000', '10100011110', '10001011110', '10111101000',
              '10111100010', '11110101000', '11110100010', '10111011110',
              '10111101110', '11101011110', '11110101110', '11010000100',
              '11010010000', '11010011100', '11000111010');
    static public function getDigit($code){
      $tableB = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~";
      $result = "";
      $sum = 0;
      $isum = 0;
      $i = 0;
      $j = 0;
      $value = 0;
      
      // check each characters
      $len = strlen($code);
      for($i=0; $i<$len; $i++){
        if (strpos($tableB, $code[$i]) === false) return("");
      }
      
      // check firsts characters : start with C table only if enought numeric
      $tableCActivated = $len> 1;
      $c = '';
      for($i=0; $i<3 && $i<$len; $i++){
        $tableCActivated &= preg_match('`[0-9]`', $code[$i]);
      }
      
      $sum = $tableCActivated ? 105 : 104;
      
      // start : [105] : C table or [104] : B table 
      $result = self::$encoding[ $sum ];
      
      $i = 0;
      while( $i < $len ){
        if (! $tableCActivated){
          $j = 0;
          // check next character to activate C table if interresting
          while ( ($i + $j < $len) && preg_match('`[0-9]`', $code[$i+$j]) ) $j++;
          
          // 6 min everywhere or 4 mini at the end
          $tableCActivated = ($j > 5) || (($i + $j - 1 == $len) && ($j > 3));
          
          if ( $tableCActivated ){
            $result .= self::$encoding[ 99 ]; // C table
            $sum += ++$isum * 99;
          }
          // 2 min for table C so need table B
        } else if ( ($i == $len - 1) || (preg_match('`[^0-9]`', $code[$i])) || (preg_match('`[^0-9]`', $code[$i+1])) ) { //todo : verifier le JS : len - 1!!! XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
          $tableCActivated = false;
          $result .= self::$encoding[ 100 ]; // B table
          $sum += ++$isum * 100;
        }
        
        if ( $tableCActivated ) {
          $value = intval(substr($code, $i, 2)); // Add two characters (numeric)
          $i += 2;
        } else {
          $value = strpos($tableB, $code[$i]); // Add one character
          $i++;
        }
        $result  .= self::$encoding[ $value ];
        $sum += ++$isum * $value;
      }
      
      // Add CRC
      $result  .= self::$encoding[ $sum % 103 ];
      
      // Stop
      $result .= self::$encoding[ 106 ];
      
      // Termination bar
      $result .= '11';
      
      return($result);
    }
  }
  