<?php

require_once(__DIR__."/DBConn.php");
require_once(__DIR__."/FactoryObject.php");
require_once(__DIR__."/JSONObject.php");

class Statement extends FactoryObject implements JSONObject {
	
	# Constants
	
	
	# Static Variables
	
	
	# Instance Variables
	private $contributionID;// int
	private $content; 		// string
	private $context; 		// string
	private $dateCreated; 	// timestamp
	
	
	# Caches
	private $paraphrases;
	private $contribution;
	
	
	# FactoryObject Methods
	protected static function gatherData($objectString, $start=FactoryObject::LIMIT_BEGINNING, $length=FactoryObject::LIMIT_ALL) {
		$data_arrays = array();
		
		// Load an empty object
		if($objectString === FactoryObject::INIT_EMPTY) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['contributionID'] = 0;
			$data_array['content'] = "";
			$data_array['context'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Load a default object
		if($objectString === FactoryObject::INIT_DEFAULT) {
			$data_array = array();
			$data_array['itemID'] = 0;
			$data_array['contributionID'] = 0;
			$data_array['content'] = "";
			$data_array['context'] = "";
			$data_array['dateCreated'] = 0;
			$data_arrays[] = $data_array;
			return $data_arrays;
		}
		
		// Set up for lookup
		$mysqli = DBConn::connect();
		
		// Load the object data
		$query_string = "SELECT statements.id AS itemID,
							   statements.contribution_id AS contributionID,
							   statements.content AS content,
							   statements.context AS context,
							   unix_timestamp(statements.date_created) as dateCreated
						  FROM statements
						 WHERE statements.id IN (".$objectString.")";
		if($length != FactoryObject::LIMIT_ALL) {
			$query_string .= "
						 LIMIT ".DBConn::clean($start).",".DBConn::clean($length);
		}
		
		$result = $mysqli->query($query_string)
			or print($mysqli->error);
		
		while($resultArray = $result->fetch_assoc()) {
			$data_array = array();
			$data_array['itemID'] = $resultArray['itemID'];
			$data_array['contributionID'] = $resultArray['contributionID'];
			$data_array['content'] = $resultArray['content'];
			$data_array['context'] = $resultArray['context'];
			$data_array['dateCreated'] = $resultArray['dateCreated'];
			$data_arrays[] = $data_array;
		}
		
		$result->free();
		return $data_arrays;
	}
	
	public function load($data_array) {
		parent::load($data_array);
		$this->contributionID = isset($data_array["contributionID"])?$data_array["contributionID"]:0;
		$this->content = isset($data_array["content"])?$data_array["content"]:"";
		$this->context = isset($data_array["context"])?$data_array["context"]:"";
		$this->dateCreated = isset($data_array["dateCreated"])?$data_array["dateCreated"]:0;
	}
	
	
	# JSONObject Methods
	public function toJSON($contentStart=null, $contentLength=null) {

		$json = '{
			"id": '.DBConn::clean($this->getItemID()).',
			"contribution_id": '.DBConn::clean($this->getContributionID()).',
			"content": '.DBConn::clean($this->getContent()).',
			"context": '.DBConn::clean($this->getContext()).'
		}';
		return $json;
	}
	
	
	# Data Methods
	public function validate() {
		return true;
	}
	
	public function save() {
		if(!$this->validate()) return;
		
		$mysqli = DBConn::connect();
		
		if($this->isUpdate()) {
			// Update an existing record
			$query_string = "UPDATE statements
							   SET statements.contribution_id = ".DBConn::clean($this->getContributionID()).",
							       statements.content = ".DBConn::clean($this->getContent()).",
							       statements.context = ".DBConn::clean($this->getContext())."
							 WHERE statements.id = ".DBConn::clean($this->getItemID());
							
			$mysqli->query($query_string) or print($mysqli->error);
		} else {
			// Create a new record
			$query_string = "INSERT INTO statements
								   (statements.id,
									statements.contribution_id,
									statements.content,
									statements.context,
									statements.date_created)
							VALUES (0,
									".DBConn::clean($this->getContributionID()).",
									".DBConn::clean($this->getContent()).",
									".DBConn::clean($this->getContext()).",
									NOW())";
			
			$mysqli->query($query_string) or print($mysqli->error);
			$this->setItemID($mysqli->insert_id);
		}
		
		// Parent Operations
		return parent::save();
	}
	
	public function delete() {
		parent::delete();
		$mysqli = DBConn::connect();
		
		// Delete this record
		$query_string = "DELETE FROM statements
							  WHERE statements.id = ".DBConn::clean($this->getItemID());
		$mysqli->query($query_string);
	}
	
	
	# Getters
	public function getContributionID() { return $this->contributionID; }

	public function getContent() { return $this->content; }

	public function getContext() { return $this->context; }

	public function getDateCreated() { return $this->dateCreated; }


	# Setters
	public function setContributionID($int) { $this->contributionID = $int; }

	public function setContent($str) { $this->content = $str; }

	public function setContext($str) { $this->context = $str; }

}

?>