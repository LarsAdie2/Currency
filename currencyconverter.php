<?php
// A simple loop to not end the program
while($number!="q"){
    // Useful variables
    $currencyarr=array("");
    $header="";
    $currentvalue="";
    $value_thirtydaysago="";
    // Number of dys back to check and how many rows in columns 
    $number_of_days=30;$amount_in_column=20;
    // This is for the program shouldnt us old values
    unset($x);
    // get an input
    $date_first = readline("Enter a date in format YYYY/MM/DD empty date uses today or q to quit:");
    // If q quit otherwise loop to next
    if($date_first=="q"){
        exit;
    }
    // If input is in right format go on or if empty, future values creates a problem as well
    // of course its a lot to require that the input is in this format but thats the problem 
    // given.
    $valid_date=validateDate($date_first);
        // Not a value of format YYYY/MM/DD
        // Doesnt allow values such as 2010/13/12 or 2019/02/29 either
    if($valid_date==TRUE){
        $date_first_asvalue=strtotime($date_first);
    }else{
        echo 'No valid date entered'.PHP_EOL;
        continue;
    }
    if($date_first==''){    
        // I convert to a epoch timestamp and from there convert to right format.
        $date_first=date('Y-m-d',strtotime('now'));
        $date_first_asvalue=strtotime('now');
        // Future dates dates cant be used by the api
    }elseif($date_first_asvalue> strtotime('now')){
        echo 'Cant enter future dates'.PHP_EOL;
        continue;
        // Cant use date before year 2000
    }elseif($date_first_asvalue< strtotime('2000-01-31')){
        echo 'Cant enter dates before year 2000'.PHP_EOL;
        continue;
    }else{
        $date_first_asvalue=strtotime($date_first);
        $date_first=date('Y-m-d',$date_first_asvalue);
    }
    // The second date
    $date_second=date('Y-m-d',$date_first_asvalue-$number_of_days*24*60*60);
    $currentvalue=get_value_date($date_first);
    $value_thirtydaysago=get_value_date($date_second);
    // Reads everything into an array if both values are valid
    foreach ($currentvalue as $key=>$value){
        if($key!=""&&$value!=""&&$value_thirtydaysago[$key]!=""){
            $currencyarr[$key]=100*sprintf('%0.4f',$value/$value_thirtydaysago[$key]);
        }
    }
    // Sort by key if not already done
    arsort($currencyarr);
    
    // Counter values
    $n=1;$i=1;
    foreach ($currencyarr as $key=>$value){
        if($key!=""){
            // Colors of some values
            if($key=='SEK'){
                $extra="\e[0;30;47m";
                $extraend="\e[0m";
            }elseif($value>105){
                $extra="\e[1;37;42m";
                $extraend="\e[0m";
            }elseif($value<95){
                $extra="\e[1;37;41m";
                $extraend="\e[0m";
            }else{
                $extra="";
                $extraend="";
            }
            
            // Makes sure all values are equally long, looks better in the table
            if(strlen($value)<7){
                $value=str_pad($value,7);
            }
            
            // There are many ways of making a table but what I do here is
            // write every value that ends in the same value on the some 
            // array element
            if($n%$amount_in_column==$i){
                $x[$i].=pad_output($n,$key,$value,$extra,$extraend);   
            }
            elseif($n%$amount_in_column==""){
                $x[$n%$amount_in_column].=pad_output($n,$key,$value,$extra,$extraend);
            }
        $i++;
        }
        // Start with new column (i.e add onto row)
        if(($i)>$amount_in_column){$i=1;}    
            $n++;
        }
        
        $amount_of_headers=ceil(count($currentvalue)/$amount_in_column);
        for($n2=0;$n2<$amount_of_headers;$n2++){   
            $header.='Item Country Percent | ';
        }
        $filling=str_pad($filling,strlen($header),'=');
        
        // Writes the table
        echo PHP_EOL.PHP_EOL."    Rate variation between $date_first and $date_second".PHP_EOL.PHP_EOL;
        echo '   '.$header.PHP_EOL;
        echo '   '.$filling.PHP_EOL;
        // Fills out the last rows in the last column if the arent equal
        $overflow=count($currencyarr)-floor(count($currencyarr)/count($x))*$amount_in_column-2;
        $nn=0;
        foreach($x as $xitem){
            if($nn>$overflow){
                echo '   '.str_pad($xitem,strlen($xitem)+21).'|'.PHP_EOL;
            }else{
                echo '   '.$xitem.PHP_EOL;
            }
            $nn++;
        }
        echo '   '.$filling.PHP_EOL;
    }


function get_value_date($date){
    $endpoint =$date;
    $access_key = 'fcec1fdd6db437c19487eb77cef7e177';

    // Initialize CURL:
    $ch = curl_init('http://data.fixer.io/api/'.$endpoint.'?access_key='.$access_key.'');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Store the data:
    $json = curl_exec($ch);
    curl_close($ch);

    // Decode JSON response:
    $exchangeRates = json_decode($json, true);

    // Access the exchange rate values, e.g. GBP:
    return $exchangeRates['rates'];
}

function pad_output($n,$key,$value,$extra,$extraend){
    if($n<10){
        $output=$extra.$n.'  :   '.$key.'   '.$value.$extraend.' | ';
    }elseif($n<100){
        $output=$extra.$n.' :   '.$key.'   '.$value.$extraend.' | ';  
    }else{
        $output=$extra.$n.':   '.$key.'   '.$value.$extraend.' | ';  
    }
    return $output;
}

function validateDate($date, $format = 'Y/m/d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>