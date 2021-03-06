<?php


function Forecast()
{
	global $db;
	$data = array(); $count =0;
	$season_length = 0;
	$result = mysqli_query($db,"SELECT * FROM monthlysale");
	$flag = false;
	$alpha = 0.8; $beta = 0.9; $gamma = 0.01; $forecast_start = 0;
	$level = 0;
	$first_season_sum = 0;
	$second_season_sum = 0;

	$pool_size = 60;
	$mut_rate = 0.0015;
	$cross_rate = 0.75;
	$chromosome_length = 7;
	
	$generation = 0;
	$fitness_sum = 0;
	$population = array();

	//Create initial population
	for($i=0; $i<$pool_size; ++$i)
	{
		$population[]=(RandomChromosome($chromosome_length,5,95) . RandomChromosome($chromosome_length,5,95) . RandomChromosome($chromosome_length,5,95)); 
	}
	
	
	//$peak=memory_get_peak_usage();

	// while(memory_get_usage()<$peak)
	// {
	// 	echo ++$blah . "   ";
	// 	//array_push($blahness, $blah);
	// }

	// data[] is filled with sales values from Database
	while($row = mysqli_fetch_array($result))
	{
			if($row['Month']>$season_length)
			{
				$season_length=$row['Month'];
			}
			
			if($row['Year']!=1 && $row['Month'] == 1 && $flag == false)
			{
				$flag = true;
				$forecast_start = $count;
			}
			++$count;
			if($count <= $season_length)
			{
				$first_season_sum+=$row['Sales'];
			}
			elseif($count <= ($season_length*2))
			{
				$second_season_sum+=$row['Sales'];
			}
			$data[]= $row['Sales']; // for negative things

	}

	while($generation < 250) // set number of generations
	{
		++$generation;
		
		$fitness = array(); $probability = array();

		for($k = 0; $k<$pool_size; ++$k)
		{
			$alpha = (bindec(substr($population[$k], 0, 7)))/100;
			$beta = (bindec(substr($population[$k], 7, 7)))/100;
			$gamma = (bindec(substr($population[$k], 14, 7)))/100;

			$level = $first_season_sum/$season_length; // OR first_season_avg
			$second_season_avg = $second_season_sum/$season_length;
			$trend = ($second_season_avg - $level)/$season_length;

			$index = array(); // to store the initial indexes of the days of the period
			for($i=0;$i<$season_length;++$i)
			{
				$index[]= $data[$i]/$level;  
			}

			$forecast_array = array(); 
			$address_index= 0;
			$temp_level = 0; $temp_trend = 0; $temp_index = 0;
			$forecast_error = array(); 



			for($i = $forecast_start; $i < $count; ++$i)
			{
				$forecast_array[]= ($level+$trend) * $index[$address_index];
				$temp_level = $level; $temp_trend = $trend; $temp_index = $index[$address_index];
				$level = ($alpha*($data[$i]/$index[$address_index])) + ((1 - $alpha)*($temp_level+$trend));
				$trend = ($gamma*($level-$temp_level))+((1-$gamma)*$temp_trend);
				$index[$address_index] = ($beta*($data[$i]/$level))+((1-$beta)*$temp_index);
				if($address_index == ($season_length-1))
				{
					$address_index = 0;
				}
				else
				{
					++$address_index;
				}

			
			}
			$length = count($forecast_array); $sum =0; $sumsq = 0;
			for($i = 0 ; $i < $length; ++$i)
			{
				$sum+=abs($data[$i+$forecast_start] - $forecast_array[$i]);
				//$forecast_error[] =abs($data[$i+$forecast_start] - $forecast_array[$i]);
				$sumsq+= pow(($data[$i+$forecast_start] - $forecast_array[$i]), 2);

			}

			$fitness[] = 1/sqrt($sumsq/$length);
			$fitness_sum+= 1/sqrt($sumsq/$length);
			$sum_prob = 0;
		}
		for($i=0;$i<$pool_size;++$i)
		{
			$probability[]=$fitness[$i]/$fitness_sum;
			$sum_prob+=$fitness[$i]/$fitness_sum;;
		}

		//CDF
		$CDF = array(); $temp_sum = 0;
		for($i=0;$i<$pool_size;++$i)
		{
			for($j=0; $j<=$i; ++$j)
			{
				$temp_sum+=$probability[$j];
			}
			$CDF[]=$temp_sum;
			$temp_sum= 0;
		}

		$fitness_sum = 0;
		
		//print_r($CDF);

		//crossover
		for($i=0; $i< $pool_size; ++$i)
		{
			$rand_1 = mt_rand(0,100)/100;
			$rand_2 = mt_rand(0,100)/100;
			//echo " " . $rand_1 . " " . $rand_2 . " " ;
			$index_rand_1 = 0;
			$index_rand_2 = 0;
			$bol_1 = false;
			$bol_2 = false;
			for($j=0;$j< $pool_size; ++$j)
			{
				if($rand_1 <= $CDF[$j] && $bol_1 == false)
				{
					$index_rand_1 = $j;
					$bol_1 = true;
				}
				if($rand_2 <= $CDF[$j] && $bol_2 == false)
				{
					$index_rand_2 = $j;	
					$bol_2 = true;
				}
			}
			if((mt_rand(0,100)/100) <= $cross_rate)
			{
				$random_point = mt_rand(0,20);
				$temp_pop_1 = substr($population[$index_rand_1], $random_point);
				$temp_pop_2 = substr($population[$index_rand_2], $random_point);

				$check_pop_1 = (substr($population[$index_rand_1], 0, $random_point) . $temp_pop_2);
				$check_pop_2 = (substr($population[$index_rand_2], 0, $random_point) . $temp_pop_1);

				$alpha1 = bindec(substr($check_pop_1,0,7)); $beta1 = bindec(substr($check_pop_1,7,7)); $gamma1 =bindec(substr($check_pop_1,14,7));
				$alpha2 = bindec(substr($check_pop_2,0,7)); $beta2 = bindec(substr($check_pop_2,7,7)); $gamma2 = bindec(substr($check_pop_2,14,7));

				if(($alpha1 >= 5 && $alpha1 <=95) && ($beta1 >= 5 && $beta1 <=95) && ($gamma1 >= 5 && $gamma1 <=95) && ($alpha2 >= 5 && $alpha2 <=95) && ($beta2 >= 5 && $beta2 <=95) && ($gamma2 >= 5 && $gamma2 <=95))
				{
					$population[$index_rand_1] = $check_pop_1;
					$population[$index_rand_2] = $check_pop_2;
				}
				
			}
			
		}
		//crossover done

		//time for some mutation baby!
		for($i = 0; $i< $pool_size; ++$i)
		{
			$blah = mt_rand(0,1000)/1000; 
			if($blah <= $mut_rate)
			{
				// echo " " . $blah . " ";
				// echo "We are mutating baby!";
				$random_point = mt_rand(0,20);

				if($population[$i][$random_point] == '0')
				{
					$temp = substr($population[$i], 0, $random_point) . '1' . substr($population[$i], $random_point+1);
					$alpha1 = bindec(substr($temp,0,7)); $beta1 = bindec(substr($temp,7,7)); $gamma1 = bindec(substr($temp,14,7));
					if(($alpha1 >= 5 && $alpha1 <=95) && ($beta1 >= 5 && $beta1 <=95) && ($gamma1 >= 5 && $gamma1 <=95))
					{
						$population[$i] = $temp;
					}
				}
				else
				{
					$temp = substr($population[$i], 0, $random_point) . '0' . substr($population[$i], $random_point+1);
					$alpha1 = bindec(substr($temp,0,7)); $beta1 = bindec(substr($temp,7,7)); $gamma1 = bindec(substr($temp,14,7));
					if(($alpha1 >= 5 && $alpha1 <=95) && ($beta1 >= 5 && $beta1 <=95) && ($gamma1 >= 5 && $gamma1 <=95))
					{
						$population[$i] = $temp;
					}	
				}
			}
		}
		//Done with mutation. Phew!
		
		
	}
	$min =1/$fitness[0]; $point = 0;
	for($i=0 ;$i< $pool_size ;++$i)
	{
		if((1/$fitness[$i])<$min)
		{
			$min = (1/$fitness[$i]);
			$point = $i;
		}
	}
	//echo (bindec(substr($population[$point], 0 ,7)))/100 . " " . (bindec(substr($population[$point], 7 ,7)))/100 . " " .(bindec(substr($population[$point], 14 ,7)))/100;
	//echo " the error = " . (1/$fitness[$point]);
	$alpha = (bindec(substr($population[$point], 0 ,7)))/100;
	$beta = (bindec(substr($population[$point], 7 ,7)))/100;
	$beta = (bindec(substr($population[$point], 14 ,7)))/100;

	// and we go again... with the forecast
	$level = $first_season_sum/$season_length; // OR first_season_avg
	$second_season_avg = $second_season_sum/$season_length;
	$trend = ($second_season_avg - $level)/$season_length;

	$index = array(); // to store the initial indexes of the days of the period
	for($i=0;$i<$season_length;++$i)
	{
		$index[]= $data[$i]/$level;  
	}
	$forecast_array = array(); 
	$address_index= 0;
	$temp_level = 0; $temp_trend = 0; $temp_index = 0;
	$forecast_error = array(); 

	for($i = $forecast_start; $i < $count; ++$i)
	{
		$forecast_array[]= ($level+$trend) * $index[$address_index];
		$forecast_error[]= $data[$i] - ($level+$trend) * $index[$address_index];
		$temp_level = $level; $temp_trend = $trend; $temp_index = $index[$address_index];
		$level = ($alpha*($data[$i]/$index[$address_index])) + ((1 - $alpha)*($temp_level+$trend));
		$trend = ($gamma*($level-$temp_level))+((1-$gamma)*$temp_trend);
		$index[$address_index] = ($beta*($data[$i]/$level))+((1-$beta)*$temp_index);

		if($address_index == ($season_length-1))
		{
			$address_index = 0;
		}
		else
		{
			++$address_index;
		}
		
	}
	for($i = $count ; $i<$count+3;++$i)
	{
		$forecast_array[]=abs(($level+($i-$count+1)*$trend)*$index[$i-$count]);

	}

	// $forecast_array[(count($forecast_array)-1)];
	 // print_r($forecast_array);
	 // echo "</br>";
	 // print_r($forecast_error);
	return $forecast_array;
	// $no_of_days =27;
	// $ini_inventory = 20000;
	// $lead_time =10
	// Inventory($forecast_array[(count($forecast_array)-1)] + 1/$fitness[$point], $forecast_start), $no_of_days;
	
}

function RandomChromosome($chromosome_length,$start, $end)
{
	$random_number = mt_rand($start,$end);
	$random_binary = decbin($random_number);
	$length = strlen($random_binary);
	while($length<$chromosome_length)
	{
		$random_binary = "0" . $random_binary;
		++$length;
	}
	return $random_binary;
}

function Inventory($no_of_days, $lead_time, $ini_inventory, $ROQ) //forecast value
{
	global $db;
	global $objinitial;
	global $objfinal;
		
	$const = 0;
	$pool_size = 80;
	$mut_rate = 0.0015;
	$cross_rate = 0.75;
	$chromosome_length = 7;
	
	$generation = 0;
	$fitness_sum = 0;
	$population = array();
		
	$inventory = getInventory($objinitial);
	$inventory_size = count($inventory);
	$temp_inventory=$inventory;

	for($i=0;$i<$pool_size;++$i)
	{
		$population[] = RandomChromosome($chromosome_length,0,127); 
	}
	$index = array();
	$stdev = array();
	$result_index = mysqli_query($db, "SELECT * FROM indexes");
	$result_stdev = mysqli_query($db, "SELECT * FROM stdevs");
	
	while($row_stdev = mysqli_fetch_array($result_stdev))
	{
		$row_ind = mysqli_fetch_array($result_index);
		$index[] = array(0, 0, $row_ind['Two'], $row_ind['Three'], $row_ind['Four'], $row_ind['Five'], $row_ind['Six'], $row_ind['Seven'], $row_ind['Eight']);
		$stdev[] = array(0, 0, $row_stdev['Two'], $row_stdev['Three'], $row_stdev['Four'], $row_stdev['Five'], $row_stdev['Six'], $row_stdev['Seven'], $row_stdev['Eight']);
	}
	
	while($generation < 100) // set number of generations
	{
		++$generation;
		$fitness = array(); $probability = array(); $pos_last = 0; $last = 0; $year =0; $month =0; $counter =0; 
		for($i = 0; $i<$pool_size; ++$i)
		{
			$const = (bindec($population[$i]))/100;
			
			$pos_last = 0; $year =1; $month =1; $sum_left_10 =0; $pos_start=0; $sum = 0;$j=0;$bol =false; $bol1=false;$no_of_days=0;
			$inventory=$temp_inventory;

			
			while($year <=4 && $month <=12)
			{
				//for the index and pos_last
				while($bol == false && $counter < ($inventory_size -1))
				{
					$sum+=$inventory[$counter][1];
					
					if($inventory[$counter+1][0] < $inventory[$counter][0])
					{
						$last = $inventory[$counter][0]; $pos_last = $counter;
						for($k=$pos_start ; $k<=$pos_last; ++$k)
						{
							$inventory[$k][]=$sum*$index[$k-$pos_start][$last%10] + ($const*$stdev[$k-$pos_start][$last%10]);
						}
						$sum = 0; $pos_start =$pos_last+1; 
						
					}

					++$counter; 

				}
				if($bol==false)
				{
					for($k=$pos_start ; $k<=$counter; ++$k)
					{
						
						$inventory[$k][]=$sum*$index[$k-$pos_start][$last%10] + ($const*$stdev[$k-$pos_start][$last%10]);
					}
					$bol=true; 
					$k=0;
				}
				//print_r($inventory); exit();

				//_______________

				$counter = $j;
				
				if($inventory[$j][0] == 1)
				{
					++$month; $sum_left_10 = 0; $bol1=false; 
					if($month > 12)
					{
						$month =1; ++$year;
					}
				}
				while($bol1 == false)
				{
					
					if($counter < $inventory_size -1)
					{
						if($inventory[$counter+1][0] < $inventory[$counter][0])
						{
							$no_of_days=$inventory[$counter][0]; $bol1=true;
						}
					}
					else {
						$no_of_days=$inventory[$counter][0]; $bol1=true;
					}
					++$counter;
				}
				
				
				$counter=$j+$no_of_days;

				while($j<$counter)
				{
					$inventory[$j][]=$ini_inventory;
					if($j+$lead_time-1<$inventory_size)
					{
						for($k=$j;$k<$j+$lead_time;++$k)
						{
							$sum_left_10+=$inventory[$j][2];
						}
					}
					else
					{
						for($k=$j;$k<$inventory_size;++$k)
						{
							$sum_left_10+=$inventory[$j][2];
						}
					}
					if($ini_inventory>=$sum_left_10)
					{
						$inventory[$j][]=true;
					}
					else
					{
						$inventory[$j][]=true;
						if($j>3)
						{
							
							if($inventory[$j-1][4] == false || $inventory[$j-2][4] == false || $inventory[$j-3][4] == false || $inventory[$j-4][4] == false)
							{
								$inventory[$j][4]=true;			
							}
						
						else
							{
								//echo "All " . " ";
								$inventory[$j][4]=false;	
							}
						}
					}
					$ini_inventory-=$inventory[$j][1];
					$sum_left_10-=$inventory[$j][1];
					if($j>=$lead_time)
					{
						if($inventory[$j-$lead_time][4] == false)
						{
							$ini_inventory+=$ROQ;
						}
					}++$j;
				}
			}
			$counter =0;
			$sum=0;
			for($l=0;$l<$j;++$l)
			{
				$sum+=$inventory[$l][3];
			}
			$sum/=$j;
			$fitness[]=1/$sum;
			$j=0;$k=0;

		}
		$fitness_sum=0;
		for($l=0;$l<$pool_size;++$l)
		{
			$fitness_sum+=$fitness[$l];
		}
		//probability
		
		for($l=0;$l<$pool_size;++$l)	
		{
			$probability[]=$fitness[$l]/$fitness_sum;

		}
		//CDF
		$CDF = array(); $temp_sum = 0;
		for($l=0;$l<$pool_size;++$l)
		{
			for($m=0; $m<=$l; ++$m)
			{
				$temp_sum+=$probability[$m];
			}
			$CDF[]=$temp_sum;
			$temp_sum= 0;
		}

		for($i=0; $i< $pool_size; ++$i)
		{
			$rand_1 = mt_rand(0,100)/100;
			$rand_2 = mt_rand(0,100)/100;
			//echo " " . $rand_1 . " " . $rand_2 . " " ;
			$index_rand_1 = 0;
			$index_rand_2 = 0;
			$bol_1 = false;
			$bol_2 = false;
			for($j=0;$j< $pool_size; ++$j)
			{
				if($rand_1 <= $CDF[$j] && $bol_1 == false)
				{
					$index_rand_1 = $j;
					$bol_1 = true;
				}
				if($rand_2 <= $CDF[$j] && $bol_2 == false)
				{
					$index_rand_2 = $j;	
					$bol_2 = true;
				}
			}
			if((mt_rand(0,100)/100) <= $cross_rate)
			{
				$random_point = mt_rand(0,6);
				$temp_pop_1 = substr($population[$index_rand_1], $random_point);
				$temp_pop_2 = substr($population[$index_rand_2], $random_point);

				$population[$index_rand_1] = (substr($population[$index_rand_1], 0, $random_point) . $temp_pop_2);
				$population[$index_rand_2] = (substr($population[$index_rand_2], 0, $random_point) . $temp_pop_1);
			}
		}
		//crossover done

		//time for some mutation baby!
		for($i = 0; $i< $pool_size; ++$i)
		{
			$blah = mt_rand(0,1000)/1000; 
			if($blah <= $mut_rate)
			{
				// echo " " . $blah . " ";
				// echo "We are mutating baby!";
				$random_point = mt_rand(0,6);

				if($population[$i][$random_point] == '0')
				{
					$population[$i] = substr($population[$i], 0, $random_point) . '1' . substr($population[$i], $random_point+1);
					// $temp = substr($population[$i], 0, $random_point) . '1' . substr($population[$i], $random_point+1);
					// $alpha1 = bindec(substr($temp,0,7)); $beta1 = bindec(substr($temp,7,7)); $gamma1 = bindec(substr($temp,14,7));
					// if(($alpha1 >= 5 && $alpha1 <=95) && ($beta1 >= 5 && $beta1 <=95) && ($gamma1 >= 5 && $gamma1 <=95))
					// {
					// 	$population[$i] = $temp;
					// }
				}
				else
				{
					$population[$i] = substr($population[$i], 0, $random_point) . '0' . substr($population[$i], $random_point+1);
					// $temp = substr($population[$i], 0, $random_point) . '0' . substr($population[$i], $random_point+1);
					// $alpha1 = bindec(substr($temp,0,7)); $beta1 = bindec(substr($temp,7,7)); $gamma1 = bindec(substr($temp,14,7));
					// if(($alpha1 >= 5 && $alpha1 <=95) && ($beta1 >= 5 && $beta1 <=95) && ($gamma1 >= 5 && $gamma1 <=95))
					// {
					// 	$population[$i] = $temp;
					// }
				}
			}
		}
		//Done with mutation. Phew!
	}
	$min =1/$fitness[0]; $point = 0;
	for($i=0 ;$i< $pool_size ;++$i)
	{
		if((1/$fitness[$i])<$min)
		{
			$min = (1/$fitness[$i]);
			$point = $i;
		}
	}
	$const = (bindec($population[$point]))/100;
	echo $const; exit();

	$inventory = $temp_inventory; $inventory_size=count($inventory);
	$year =0 ; $month =0;$j=0; $counter=0;$sum=0; $bol=false;$k=0;
	while($year <=4 && $month <=12)
	{
		while($bol == false && $counter < ($inventory_size -1))
		{
					$sum+=$inventory[$counter][1];
					
					if($inventory[$counter+1][0] < $inventory[$counter][0])
					{
						$last = $inventory[$counter][0]; $pos_last = $counter;
						for($k=$pos_start ; $k<=$pos_last; ++$k)
						{
							$inventory[$k][]=$sum*$index[$k-$pos_start][$last%10] + ($const*$stdev[$k-$pos_start][$last%10]);
						}
						$sum = 0; $pos_start =$pos_last+1; 
						
					}

					++$counter; 

				}
				if($bol==false)
				{
					for($k=$pos_start ; $k<=$counter; ++$k)
					{
						
						$inventory[$k][]=$sum*$index[$k-$pos_start][$last%10] + ($const*$stdev[$k-$pos_start][$last%10]);
					}
					$bol=true; 
					$k=0;
				}
			print_r($inventory); exit();
			//_______________
		$counter = $j;
		
		if($inventory[$j][0] == 1)
		{
			++$month; $sum_left_10 = 0; $bol1=false; 
			if($month > 12)
			{
				$month =1; ++$year;
			}
			echo $month . " ";
		}
		while($bol1 == false)
		{
			
			if($counter < $inventory_size -1)
			{
				if($inventory[$counter+1][0] < $inventory[$counter][0])
				{
					$no_of_days=$inventory[$counter][0]; $bol1=true;
				}
			}
			else {
				$no_of_days=$inventory[$counter][0]; $bol1=true;
			}
			++$counter;
		}
		
	
		$counter=$j+$no_of_days;
		while($j<$counter)
		{
			$inventory[$j][]=$ini_inventory;
			if($j+$lead_time-1<$inventory_size)
			{
				for($k=$j;$k<$j+$lead_time;++$k)
				{
					$sum_left_10+=$inventory[$j][2];
				}
			}
			else
			{
				for($k=$j;$k<$inventory_size;++$k)
				{
					$sum_left_10+=$inventory[$j][2];
				}
			}
			if($ini_inventory>=$sum_left_10)
			{
				$inventory[$j][]=true;
			}
			else
			{
				$inventory[$j][]=true;
				if($j>3)
				{
						
					if($inventory[$j-1][4] == false || $inventory[$j-2][4] == false || $inventory[$j-3][4] == false || $inventory[$j-4][4] == false)
					{
						$inventory[$j][4]=true;			
					}
				
				else
					{
						//echo "All " . " ";
						$inventory[$j][4]=false;	
					}
				}
			}
			$ini_inventory-=$inventory[$j][1];
			$sum_left_10-=$inventory[$j][1];
			if($j>=$lead_time)
			{
				if($inventory[$j-$lead_time][4] == false)
				{
					$ini_inventory+=$ROQ;
				}
			}++$j;
		}
	}
	//print_r($inventory);
}

function getInventory($objinitial)
{
	foreach ($objinitial->getWorksheetIterator() as $worksheet) 
	{
    	$highestRow         = $worksheet->getHighestRow(); // e.g. 10
    	$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
    	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    	$nrColumns = ord($highestColumn) - 64;
    	for ($row = 2; $row <= $highestRow; ++$row) 
    	{
    		$sale =$worksheet->getCellByColumnAndRow(1, $row)->getValue();
    		$rowValue=$worksheet->getCellByColumnAndRow(0, $row)->getValue();
       		$inventory[]= array($rowValue,$sale);
		}
	break;
	}
	return $inventory;
}

function monthlysales()
{
	global $db;
	global $objinitial;
	mysqli_query($db,"TRUNCATE TABLE monthlysale");
	

	foreach ($objinitial->getWorksheetIterator() as $worksheet)
	{
    	// $worksheetTitle     = $worksheet->getTitle();
    	$highestRow         = $worksheet->getHighestRow(); // e.g. 10
    	$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
    	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    	$nrColumns = ord($highestColumn) - 64;
    	
    	$col = 1; $counter=1; $sum=0;
    	for ($row = 2; $row <= $highestRow; ++ $row) 
    	{
    		if($row!=$highestRow)
    		{
    			$sale =$worksheet->getCellByColumnAndRow($col, $row)->getValue();
    			$rowValue=$worksheet->getCellByColumnAndRow(0, $row)->getValue();
    			$nextRowValue=$worksheet->getCellByColumnAndRow(0, ($row+1))->getValue();
    			$sum=$sum+ $sale;
    			if($nextRowValue<$rowValue)
    			{
    				$Year=((int)(($counter-1)/12))+1;
    				$Month=$counter%12;
    				if($Month==0){$Month=12;}
    				mysqli_query($db,"INSERT INTO 
    				 	monthlysale (Year, Month, Sales)
    				 	VALUES ('$Year', '$Month' , '$sum')");

    				// echo $counter . " " . $Year . " " . $Month . " " .$sum;
    				// echo "\r\n";

    				$sum=0;
    				++$counter;
    			}
    		}
    	else
    	{
    		if($counter>48) // this has to be inputted at the main screen. Otherwise weird things will happen
    		{
    			continue;
    		}
    		$sale =$worksheet->getCellByColumnAndRow($col, $row)->getValue();
    		$sum=$sum+$sale;
    		$Year=((int)(($counter-1)/12))+1;
    		$Month=$counter%12;
    		if($Month==0){$Month=12;}
    		 mysqli_query($db,"INSERT INTO 
    			 	monthlysale (Year, Month, Sales)
    			 	VALUES ('$Year', '$Month' , '$sum')");

    		// echo $counter . " " . $Year . " " . $Month . " ". $sum;
    		// echo "\r\n";

    		$sum=0;
    		++$counter;
    	}
        
    }break;
    
	}
}

function index_stdev()
{
	global $db;
	global $objinitial;
	mysqli_query($db,"TRUNCATE TABLE indexes");
	mysqli_query($db,"TRUNCATE TABLE stdevs");

	foreach ($objinitial->getWorksheetIterator() as $worksheet) 
	{
    	// $worksheetTitle     = $worksheet->getTitle();
    	$highestRow         = $worksheet->getHighestRow(); // e.g. 10
    	//echo $highestRow;
    	$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
    	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
    	$nrColumns = ord($highestColumn) - 64;
    	$saleByDay = new SplFixedArray(28);
    	$countDay = new SplFixedArray(28);
    	$avgSaleInDay = new SplFixedArray(28);
    	$sumAvgSale=0;
    	$stdevterm = new SplFixedArray(28);
    	for($i=0;$i<28;$i++)
    	{
    		$saleByDay[$i]=0;
    		$countDay[$i]=0;
            $stdevterm[$i]=0;
    	}
		$col = 1; $counter=1; $sum=0;
		//daily sale and count
    	for ($row = 2; $row <= $highestRow; ++ $row) 
    	{
       		$sale =$worksheet->getCellByColumnAndRow($col, $row)->getValue();
    		$rowValue=$worksheet->getCellByColumnAndRow(0, $row)->getValue();
        	$saleByDay[$rowValue-1]+=$sale; //daily sale
    		$countDay[$rowValue-1]=$countDay[$rowValue-1]+1; //count of a day


   		 }
    	//avg sale in a day and sum of avg sales of all "days"
    	for($i=0;$i<28;$i++)
    	{
    	    if($countDay[$i]!=0)
    	    {
        		$avgSaleInDay[$i]=$saleByDay[$i]/$countDay[$i];
        		$sumAvgSale+=$avgSaleInDay[$i];
        	}
    	}
    	//index of days
    	$indexOfDays = new SplFixedArray(28);
    	for($i=0;$i<28;$i++)
    	{
    	    $indexOfDays[$i]=$avgSaleInDay[$i]/$sumAvgSale;
    	}


    	//MAIN: standard deviation of days
    
    	$bol = true;
    	$sumOfWeights = 0;
      
    	$result = mysqli_query($db,"SELECT * FROM monthlysale");
    	if (!$result) 
    	{
    	    printf("Error: %s\n", mysqli_error($db));
    	    exit();
    	}
    	$rowArray = mysqli_fetch_array($result);
    	$weight=$rowArray['Sales'];
    	$sumOfWeights+=$weight;
   
  		//PRODUCING THE SUMMATION PART OF THE STANDARD DEVIATION FORMULA
    	for ($row = 2; $row <= $highestRow; $row++) 
    	{
    	      
    	    $sale =$worksheet->getCellByColumnAndRow($col, $row)->getValue();
    	    $rowValue=$worksheet->getCellByColumnAndRow(0, $row)->getValue();
    	    $stdevterm[$rowValue-1]+= pow($sale-$avgSaleInDay[$rowValue-1],2)*$weight;
    	    if($row!=$highestRow && $bol=true)
    	    {
    	        $nextRowValue=$worksheet->getCellByColumnAndRow(0, ($row+1))->getValue();
    	        if($rowValue>$nextRowValue)
    	        {
    	            $bol=false;
    	        }
    	    }
    	    if($bol==false)
    	    {
    	        $rowArray = mysqli_fetch_array($result);
    	        $weight=$rowArray['Sales'];
    	        $bol=true;
    	        $sumOfWeights+=$weight;
    	    }
    	}
    	$stdev= new SplFixedArray(28);
    	for($i=0;$i<28;$i++)
    	{
    	    $stdev[$i] = sqrt($stdevterm[$i]/(($countDay[$i]-1)*$sumOfWeights));
    	}

    	//arrays of array to store the final
    	$INDEX = new SplFixedArray(7);
    	$DEVIATION = new SplFixedArray(7);
    	$sumOfLastIndex = new SplFixedArray(7);
    	$sumOfLastDeviation = new SplFixedArray(7);
    	$sumOfAllIndex = 0;
    	$sumOfAllDeviation = 0;


    	//SUM OF LAST AND ALL ENTRIES TO DISTRIBUTE VALUES FOR SEVEN, SIX, FIVE...
    	for($i=0;$i<7;$i++)
    	{
    	    $sumOfLastDeviation[$i]=0;
    	    $sumOfLastIndex[$i]=0;
    	    $INDEX[$i]= new SplFixedArray(28);
    	    $DEVIATION[$i]=new SplFixedArray(28);

        	for($j=27;$j>=0;$j--)
	        {
    	        $INDEX[$i][$j]=0;
    	        $DEVIATION[$i][$j]=0;
    	        if($j>(27-$i))
    	        {
    	            $sumOfLastIndex[$i]+=$indexOfDays[$j];
    	            $sumOfLastDeviation[$i]+=$stdev[$j];
    	        }
    	        $sumOfAllIndex+=$indexOfDays[$j];
    	        $sumOfAllDeviation+=$stdev[$j];
    	    }
    	}

    	//CALCULATING INDEXES AND DEVIATIONS FOR ALL DAY SERIES
    	for($i=0;$i<7;$i++)
    	{
    	    for($j=0;$j<(28-$i);$j++)
    	    {
    	        $INDEX[$i][$j]=$indexOfDays[$j]+(($indexOfDays[$i]/$sumOfAllIndex)*$sumOfLastIndex[$i]);
    	        $DEVIATION[$i][$j]=$stdev[$j]+(($stdev[$i]/$sumOfAllDeviation)*$sumOfLastDeviation[$i]);
    	    }
    	}
    	
    	//INSERTING INDEXES AND DEVIATIONS IN DATABASE
    	for($i=0;$i<28;$i++)
    	{
    	    mysqli_query($db,"INSERT INTO 
    	        indexes (Eight, Seven, Six, Five, Four, Three, Two)
    	        VALUES (\"{$INDEX[0][$i]}\", \"{$INDEX[1][$i]}\", \"{$INDEX[2][$i]}\" ,\"{$INDEX[3][$i]}\", \"{$INDEX[4][$i]}\", \"{$INDEX[5][$i]}\", \"{$INDEX[6][$i]}\")");
    	    mysqli_query($db,"INSERT INTO 
    	        stdevs (Eight, Seven, Six, Five, Four, Three, Two)
    	        VALUES (\"{$DEVIATION[0][$i]}\", \"{$DEVIATION[1][$i]}\", \"{$DEVIATION[2][$i]}\" ,\"{$DEVIATION[3][$i]}\", \"{$DEVIATION[4][$i]}\", \"{$DEVIATION[5][$i]}\", \"{$DEVIATION[6][$i]}\")");

   		}	
    }
}