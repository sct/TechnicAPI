<?php

require_once(APPPATH.'libraries/REST_Controller.php'); 
require_once(APPPATH.'third_party/Spyc.php'); 

class Modpack extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->rest_format = "json";
	}
	
	public function index_get()
	{
		$yaml = file_get_contents($this->config->item('repo_url').'modpacks.yml');
		$modpacks = Spyc::YAMLLoad($yaml);
		$modpacks = $modpacks['modpacks'];
		
		$plist = array();
		$blocked_packs = array('technicssp','custom1','custom2','custom3');
		foreach ($modpacks as $key => $modpack)
		{
			//array_push($plist, $key);
			if (!in_array($key, $blocked_packs))
				$plist[$key] = $modpack;
		}
		$pmod = array('modpacks' => $plist,'mirror_url' => $this->config->item('repo_url'));
		
		$this->response($pmod);
	}

	public function details_get()
	{
		if ($this->uri->segment(3) != NULL) {
			$request = $this->uri->segment(3);
		} else { $request = NULL; }

		$yaml = file_get_contents($this->config->item('repo_url').$this->uri->segment(2).'/modpack.yml');
		$modpackData = Spyc::YAMLLoad($yaml);
				
		switch ($request) 
		{
			case "MD5":
				$md5 = array("MD5" => md5($yaml));
				$this->response($md5);
				break;
			case "build":
				if ($this->uri->segment(4) == NULL OR !array_key_exists($this->uri->segment(4),$modpackData['builds'])) {
					$this->response(array('error' => 'Build not found'),404);
				} else {
					$buildRequest = $this->uri->segment(4);
					$versionData = $modpackData['builds'][$buildRequest];
					$this->response($versionData);
				}
				break;
			default:
				// Generate build list
				$modpackBuilds = array();
				foreach ($modpackData['builds'] as $build => $value)
				{
					array_push($modpackBuilds,$build);
				}

				$modpack = array(
								"name" => $this->uri->segment(2),
								"icon_md5" => md5(file_get_contents($this->config->item('repo_url').$this->uri->segment(2)."/resources/icon.png")),
								"logo_md5" => md5(file_get_contents($this->config->item('repo_url').$this->uri->segment(2)."/resources/logo_180.png")),
								"background_md5" => md5(file_get_contents($this->config->item('repo_url').$this->uri->segment(2)."/resources/background.jpg")),
								"recommended" => $modpackData['recommended'],
								"latest" => $modpackData['latest'],
								"builds" => $modpackBuilds
								);
				$this->response($modpack);
		}
	}

}

?>