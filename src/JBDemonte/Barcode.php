<?php
/**
 *  BarCode Coder Library (BCC Library)
 *  BCCL Version 2.0.1
 *  Porting : Barcode PHP
 *            DEMONTE Jean-Baptiste  
 *  Date    : September 25, 2010
 *  
 *  
 *  Author  : DEMONTE Jean-Baptiste (firejocker)
 *            HOUREZ Jonathan
 *  Contact : jbdemonte @ gmail.com
 *  Web site: http://barcode-coder.com/
 *  dual licence :  http://www.cecill.info/licences/Licence_CeCILL_V2-fr.html
 *                  http://www.gnu.org/licenses/gpl.html
 *
 *  Managed :
 *     
 *    standard 2 of 5 (std25)
 *    interleaved 2 of 5 (int25)
 *    ean 8 (ean8)
 *    ean 13 (ean13)   
 *    code 11 (code11)
 *    code 39 (code39)
 *    code 93 (code93)
 *    code 128 (code128)  
 *    codabar (codabar)
 *    msi (msi)
 *    datamatrix (datamatrix)
 *  
 *  Output :
 *   
 *    GD
 *    FPDF 
 */

namespace JBDemonte;

use JBDemonte\Barcodes as Barcodes;
  
  class Barcode {
  
    static public function gd($res, $color, $x, $y, $angle, $type, $datas, $width = null, $height = null){
      return self::_draw(__FUNCTION__, $res, $color, $x, $y, $angle, $type, $datas, $width, $height);
    }
    
    static public function fpdf($res, $color, $x, $y, $angle, $type, $datas, $width = null, $height = null){
      return self::_draw(__FUNCTION__, $res, $color, $x, $y, $angle, $type, $datas, $width, $height);
    }
    
    static private function _draw($call, $res, $color, $x, $y, $angle, $type, $datas, $width, $height){
      $digit = '';
      $hri   = '';
      $code  = '';
      $crc   = true;
      $rect  = false;
      $b2d   = false;
      
      if (is_array($datas)){
        foreach(array('code' => '', 'crc' => true, 'rect' => false) as $v => $def){
        $$v = isset($datas[$v]) ? $datas[$v] : $def;
        }
        $code = $code;
      } else {
        $code = $datas;
      }
      if ($code == '') return false;
      $code = (string) $code;
      
      $type = strtolower($type);
      
      switch($type){
        case 'std25':
        case 'int25':
          $digit = Barcodes\BarcodeI25::getDigit($code, $crc, $type);
          $hri = Barcodes\BarcodeI25::compute($code, $crc, $type);
        break;
        case 'ean8':
        case 'ean13':
          $digit = Barcodes\BarcodeEAN::getDigit($code, $type);
          $hri = Barcodes\BarcodeEAN::compute($code, $type);
        break;
        case 'code11':
          $digit = Barcodes\Barcode11::getDigit($code);
          $hri = $code;
        break;
        case 'code39':
          $digit = Barcodes\Barcode39::getDigit($code);
          $hri = $code;
        break;
        case 'code93':
          $digit = Barcodes\Barcode93::getDigit($code, $crc);
          $hri = $code;
        break;
        case 'code128':
          $digit = Barcodes\Barcode128::getDigit($code);
          $hri = $code;
        break;
        case 'codabar':
          $digit = Barcodes\BarcodeCodabar::getDigit($code);
          $hri = $code;
        break;
        case 'msi':
          $digit = Barcodes\BarcodeMSI::getDigit($code, $crc);
          $hri = Barcodes\BarcodeMSI::compute($code, $crc);
        break;
        case 'datamatrix':   
          $digit = Barcodes\BarcodeDatamatrix::getDigit($code, $rect);
          $hri = $code;
          $b2d = true;
        break;
      }
      
      if ($digit == '') return false;
      
      if ( $b2d ){
        $width = is_null($width) ? 5 : $width;
        $height = $width;
      } else {
        $width = is_null($width) ? 1 : $width;
        $height = is_null($height) ? 50 : $height;
        $digit = self::bitStringTo2DArray($digit);
      }
      
      if ( $call == 'gd' ){
        $result = self::digitToGDRenderer($res, $color, $x, $y, $angle, $width, $height, $digit);
      } else if ( $call == 'fpdf' ){
        $result = self::digitToFPDFRenderer($res, $color, $x, $y, $angle, $width, $height, $digit);
      }
      
      $result['hri'] = $hri;
      return $result;
    }
    
    // convert a bit string to an array of array of bit char
    private static function bitStringTo2DArray( $digit ){
      $d = array();
      $len = strlen($digit);
      for($i=0; $i<$len; $i++) $d[$i] = $digit[$i];
      return(array($d));
    }
    
    // GD barcode renderer
    private static function digitToGDRenderer($gd, $color, $xi, $yi, $angle, $mw, $mh, $digit){
      $lines = count($digit);
      $columns = count($digit[0]);
      $angle = deg2rad(-$angle);
      $cos = cos($angle);
      $sin = sin($angle);
      
      self::_rotate($columns * $mw / 2, $lines * $mh / 2, $cos, $sin , $x, $y);
      $xi -=$x;
      $yi -=$y;
      
      for($y=0; $y<$lines; $y++){
        $len = 0;
        $current = $digit[$y][0];
        for($x=0; $x<$columns; $x++){
          if ($current == $digit[$y][$x]) {
            $len++;
          } else {
            if ($current == '1'){
              $px = $len * $mw;
              $xt = $xi + ($x - $len) * $mw;
              if ($angle == 0){
                if ($px > 2){
                  imagefilledrectangle($gd, $xt, $yi + $y * $mh, $xt + $px - 1, $yi + ($y + 1) * $mh, $color);
                } else {
                  for($i = 0; $i < $px; $i++){
                    imageline($gd, $xt + $i, $yi + $y * $mh, $xt + $i, $yi + ($y + 1) * $mh, $color);
                  } 
                }
              } else {
                  for($i = 0; $i < $px; $i++){
                    self::_rotate($xt + $i - $xi, $y * $mh, $cos, $sin , $x1, $y1);
                    self::_rotate($xt + $i - $xi, ($y + 1) * $mh, $cos, $sin , $x2, $y2);
                    imageline($gd, $xi + $x1, $yi + $y1, $xi + $x2, $yi + $y2, $color);
                  }
              }
            }
            $current = $digit[$y][$x];
            $len=1;
          }
        }
        if ( ($len > 0) && ($current == '1') ){
          $px = $len * $mw;
          $xt = $xi + ($columns - $len) * $mw;
          $y = $lines - 1;
          if ($angle == 0){
            if ($px > 2){
              imagefilledrectangle($gd, $xt, $yi + $y * $mh, $xt + $px - 1, $yi + ($y + 1) * $mh, $color);
            } else {
              for($i = 0; $i < $px; $i++){
                imageline($gd, $xt + $i, $yi + $y * $mh, $xt + $i, $yi + ($y + 1) * $mh, $color);
              } 
            }
          } else {
              for($i = 0; $i < $px; $i++){
                self::_rotate($xt + $i - $xi, $y * $mh, $cos, $sin , $x1, $y1);
                self::_rotate($xt + $i - $xi, ($y + 1) * $mh, $cos, $sin , $x2, $y2);
                imageline($gd, $xi + $x1, $yi + $y1, $xi + $x2, $yi + $y2, $color);
              }
          }
          
        }
      }
      return self::result($xi, $yi, $columns, $lines, $mw, $mh, $cos, $sin);
    }
    
    // GD barcode renderer
    private static function digitToFPDFRenderer($pdf, $color, $xi, $yi, $angle, $mw, $mh, $digit){
      $lines = count($digit);
      $columns = count($digit[0]);
      $angle = deg2rad(-$angle);
      $cos = cos($angle);
      $sin = sin($angle);
      
      $pdf->SetLineWidth($mw);
      
      if (!is_array($color)){
        if (preg_match('`([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})`i', $color, $m)){
          $color = array(hexdec($m[1]),hexdec($m[2]),hexdec($m[3]));
        } else {
          $color = array(0,0,0);
        }
      }
      $color = array_values($color);
      $pdf->SetDrawColor($color[0],$color[1],$color[2]);
      $pdf->SetFillColor($color[0],$color[1],$color[2]);
      
      self::_rotate($columns * $mw / 2, $lines * $mh / 2, $cos, $sin , $x, $y);
      $xi -=$x;
      $yi -=$y;
      
      for($y=0; $y<$lines; $y++){
        $len = 0;
        $current = $digit[$y][0];
        for($x=0; $x<$columns; $x++){
          if ($current == $digit[$y][$x]) {
            $len++;
          } else {
            if ($current == '1'){
              $px = $len ;
              $xt = $xi + ($x - $len) * $mw;
              if ($angle == 0){
                if ($px > 2){
                  $pdf->rect($xt, $yi + $y * $mh, $px - 1, $mh,  'F');
                } else {
                  for($i = 0; $i < $px; $i++){
                    $pdf->line($xt + $i, $yi + $y * $mh, $xt + $i, $yi + ($y + 1) * $mh);
                  } 
                }
              } else {
                  for($i = 0; $i < $px; $i++){
                    self::_rotate($xt + $i - $xi, $y * $mh, $cos, $sin , $x1, $y1);
                    self::_rotate($xt + $i - $xi, ($y + 1) * $mh, $cos, $sin , $x2, $y2);
                    $pdf->line($xi + $x1, $yi + $y1, $xi + $x2, $yi + $y2);
                  }
              }
            }
            $current = $digit[$y][$x];
            $len=1;
          }
        }
        if ( ($len > 0) && ($current == '1') ){
          $px = $len ;
          $xt = $xi + ($columns - $len) * $mw;
          $y = $lines - 1;
          if ($angle == 0){
            if ($px > 2){
              $pdf->rect($xt, $yi + $y * $mh, $px - 1, $mh,  'F');
            } else {
              for($i = 0; $i < $px; $i++){
                $pdf->line($xt + $i, $yi + $y * $mh, $xt + $i, $yi + ($y + 1) * $mh);
              } 
            }
          } else {
              for($i = 0; $i < $px; $i++){
                self::_rotate($xt + $i - $xi, $y * $mh, $cos, $sin , $x1, $y1);
                self::_rotate($xt + $i - $xi, ($y + 1) * $mh, $cos, $sin , $x2, $y2);
                $pdf->line($xi + $x1, $yi + $y1, $xi + $x2, $yi + $y2);
              }
          }
          
        }
      }
      return self::result($xi, $yi, $columns, $lines, $mw, $mh, $cos, $sin);
    }
    
    static private function result($xi, $yi, $columns, $lines, $mw, $mh, $cos, $sin){
      self::_rotate(0, 0, $cos, $sin , $x1, $y1);
      self::_rotate($columns * $mw, 0, $cos, $sin , $x2, $y2);
      self::_rotate($columns * $mw, $lines * $mh, $cos, $sin , $x3, $y3);
      self::_rotate(0, $lines * $mh, $cos, $sin , $x4, $y4);
      
      return array(
        'width' => $columns * $mw,
        'height'=> $lines * $mh,
        'p1' => array(
          'x' => $xi + $x1,
          'y' => $yi + $y1
        ),
        'p2' => array(
          'x' => $xi + $x2,
          'y' => $yi + $y2
        ),
        'p3' => array(
          'x' => $xi + $x3,
          'y' => $yi + $y3
        ),
        'p4' => array(
          'x' => $xi + $x4,
          'y' => $yi + $y4
        )
      );
    }
    
    static private function _rotate($x1, $y1, $cos, $sin , &$x, &$y){
      $x = $x1 * $cos - $y1 * $sin;
      $y = $x1 * $sin + $y1 * $cos;
    }
    
    static public function rotate($x1, $y1, $angle , &$x, &$y){
      $angle = deg2rad(-$angle);
      $cos = cos($angle);
      $sin = sin($angle);
      $x = $x1 * $cos - $y1 * $sin;
      $y = $x1 * $sin + $y1 * $cos;
    }
}