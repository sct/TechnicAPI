<?php

require_once(APPPATH.'libraries/REST_Controller.php'); 
require_once(APPPATH.'third_party/Spyc.php'); 

class Cache extends REST_Controller {

	public function index_get()
	{
		$yaml = file_get_contents(FCPATH.'checksum.yml');
		$checksum = Spyc::YAMLLoad($yaml);
		echo "<pre>";
		print_r($checksum);
	}

	public function update_get()
	{
		$checksum = file_get_contents('http://mirror.technicpack.net/Technic/CHECKSUM.md5');
		$md5Array = explode("\n",$checksum);
		$finalized = array();
		foreach ($md5Array as $entry)
		{
			$split = explode('|',$entry);
			$md5 = $split[0];
			if (isset($split[1]))
			{
			$directory = explode("\\",$split[1]);
				if ($directory[0] == "mods")
				{
					if (!array_key_exists($directory[1],$finalized)) {
						$finalized[$directory[1]] = array();
					}
					$versionSplit = explode("-",$directory[2]);

					// This is to remedy mods with multiple dashes
					$splitCount = count($versionSplit) - 1;

					$modversion = str_replace("\r", "", $versionSplit[$splitCount]);
					$modversion = str_replace(".zip", "", $modversion);
					$finalized[$directory[1]][$modversion] = $md5;
				}
			}
		}
		$yaml = Spyc::YAMLDump($finalized);

		$ymlFileName = FCPATH."checksum.yml";
		$fstream = fopen($ymlFileName,'w');
		fwrite($fstream,$yaml);
		fclose($fstream);
	}

	public function mod_get()
	{
		$yaml = file_get_contents(FCPATH.'checksum.yml');
		$checksum = Spyc::YAMLLoad($yaml);

		$success = 0;

		if ($this->uri->segment(3) != NULL)
		{
			$modName = $this->uri->segment(3);
			if (array_key_exists($modName,$checksum))
			{
				if ($this->uri->segment(4) != NULL)
				{
					$modVersion = $this->uri->segment(4);
					if ($this->uri->segment(5) != NULL && $this->uri->segment(5) == "MD5")
					{
						if (array_key_exists($modVersion,$checksum[$modName]))
						{
							// HOLY GOD NESTED IFS ARE TERRIBLE KILL ME NOW
							$this->response(array("MD5" => $checksum[$modName][$modVersion]));
							$success = 1;
							// hooray
						}
					} else {
						$this->response(array($modName => "http://mirror.technicpack.net/Technic/mods/".$modName."/".$modName."-".$modVersion));
						$success = 1;
					}
				}
			}
		}

		if ($success == 0)
			$this->response(array("error" => "There was an error in your request"),404);
	}
}

?>