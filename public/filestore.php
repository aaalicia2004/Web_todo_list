<?php 

class Filestore {

    public $filename = '';
    private $is_csv = false; //this assumes the file is not csv

    function __construct($filename = '') 
    {
        $this->filename = $filename;

        if (substr($this->filename, -3) == 'csv') {
    		return $this->is_csv = true;
    	}
    }
    	
  	public function read()
    {
    	if ($this->is_csv== TRUE)
    	{
    		return $this->read_csv();
    	}
    	else
    	{
    		return $this->read_lines();
    	}
    }

    public function write($array)
    {
    	if ($this->is_csv == TRUE)
    	{
    		return $this->write_csv($array);
    	}
    	else
    	{
    		return $this->write_lines($array);
    	}
    }

    /**
     * Returns array of lines in $this->filename
     */
    private function read_lines() //was previously named--function Open_Read_File($filename)
	{
	    $dataListArray = [];

	    if (is_readable($this->filename) && filesize($this->filename) > 0)
	    {
	        $filesize = filesize($this->filename);
	        $read = fopen($this->filename, 'r');
	        $dataListString=trim(fread($read, $filesize));
	        $dataListArray = explode ("\n" , $dataListString);
	        fclose($read);   
	    }
	    return $dataListArray;
    }
    /**
     * Writes each element in $array to a new line in $this->filename
     */
    private function write_lines($array) //was previously named --function save_file($filename, $contents)
		{
		    $handle = fopen($this->filename, 'w');
		   	$dataListString = implode("\n", $array);
		    fwrite($handle, $dataListString);
		    fclose($handle);
		}

    /**
     * Reads contents of csv $this->filename, returns an array
     */
    private function read_csv() //public function read_address_book()
    {  //code to read file $this->filename
        $handle = fopen($this->filename, 'r');
        $address_book= []; //read each line of CSV and add rows to empty addresses array
        
        
        while(!feof($handle)){
            $row = fgetcsv($handle);
            if(is_array($row)){
                $address_book[] = $row; //does the same thing as array_push($address_book, $row); 
            }
        }
        fclose($handle);
        return $address_book;
    }

    /**
     * Writes contents of $array to csv $this->filename
     */
    private function write_csv($array) //public function write_address_book($addresses_array) //code to write $addresses_array to file $this->filenam
    {
        if(is_writeable($this->filename)){
            $handle = fopen($this->filename, 'w');
            foreach($addresses_array as $subArray){
                fputcsv($handle, $subArray); 
            }
        }
        fclose($handle);
    }

}




