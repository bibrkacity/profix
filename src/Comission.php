<?php

class Comission
{
    private $filename;
    private $errors;
    private $rates;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public static function fromString(string $filename): self
    {
        return new self($filename);
    }



    public function  parser()
    {
        $this->errors = [];

        $this->get_rates();

        if($this->rates === false)
            return ['rows'=>[],'errors'=>$this->errors];

        $result = [];

        $text = file_get_contents($this->filename);

        if($text === false)
            $this->errors[] = 'Can\'t read file '. $this->filename;
        else
            $result = $this->fetch_rows($text);

        return ['rows'=>$result,'errors'=>$this->errors];

    }

        /**************************
         *   P R I V A T E
         **************************
         */

    private function fetch_rows($text)
    {
        $re = '/\{[^\}]+\}/u';

        preg_match_all($re,$text, $matches);

        $rows = $matches[0];

        $result=[];

        foreach ($rows as $n => $row)
        {
            $res = $this->one_row($n,$row);
            if ($res !== null)
                $result[] = $res;
        }

        return $result;

    }

    private function one_row($n,$row)
    {
        $json = json_decode($row);

        if($json === null)
        {
            $this->errors[] = 'Can\'t decode string  '. $n . ': '.$row;
            return null;
        }

        $binResults = file_get_contents('https://lookup.binlist.net/' .$json->bin);

        if( $binResults === false )
            {
                $this->errors[] = 'Can\'t get bin resource from url  https://lookup.binlist.net/' .$json->bin;
                return null;
            }

        $r = json_decode($binResults);

        $isEu = $this->isEu($r->country->alpha2);

        $rate = 1;

        if( $json->currency != 'EUR')
            $rate = isset($this->rates['rates'][$json->currency]) ? $this->rates['rates'][$json->currency] : 0;

        if($rate === 0)
            if($json === null)
            {
                $this->errors[] = 'Can\'t get rate of  '. $json->currency;
                return null;
            }

        $amount_eur = $json->amount / $rate;
        $comission = $isEu == 'yes' ? 0.01 : 0.02;

        return $amount_eur*$comission;

    }

    private function get_rates()
    {
       $json =  file_get_contents('https://api.exchangeratesapi.io/latest');

       if($json === false ) {
           $errors[] = 'Can\'t get rates';
           $this->rates = false;
       }

       $this->rates = json_decode($json,true);

    }


    private function isEu($c)
    {
        $eurozone = [
             'AT'
            ,'BE'
            ,'BG'
            ,'CY'
            ,'CZ'
            ,'DE'
            ,'DK'
            ,'EE'
            ,'ES'
            ,'FI'
            ,'FR'
            ,'GR'
            ,'HR'
            ,'HU'
            ,'IE'
            ,'IT'
            ,'LT'
            ,'LU'
            ,'LV'
            ,'MT'
            ,'NL'
            ,'PO'
            ,'PT'
            ,'RO'
            ,'SE'
            ,'SI'
            ,'SK'
            ];

        $result = in_array( $c,$eurozone ) ? 'yes' : 'no';

        return $result;
    }

}