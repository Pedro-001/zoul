<?php
include "initializer.php";

//Session is set to "PvUsrSess"

class Services {

	public $conn;

	function __construct()
	{
		global $extras_class;
		$this->conn = $extras_class->database();
	}

	//Get list of all the services
	public function get_disciplines_list($user_id,$status){
		global $extras_class;
		$services_list_array;

		$response_array = $extras_class->response("false","e1","Hubo un problema interno (500).", "both");
		$query = "SELECT * FROM users WHERE user_id = '$user_id'";
		$result = mysqli_query($this->conn, $query);

		/* Ver si el usuario existe */
		if (mysqli_num_rows($result)) {
		  $query_list = "SELECT * FROM disciplines ORDER BY id DESC";
		  $result_list = mysqli_query($this->conn, $query_list);

		  if (mysqli_num_rows($result_list)) {
		  	  while($row = mysqli_fetch_assoc($result_list)) {
		  	  	$services_list_array[] = $row;
		  	  }
		  }
		}

		$response_array = $extras_class->response("true","sc1",$services_list_array, "console");

		return $response_array;
	}
	
	//Get list of all the services
	public function get_coaches_list($user_id,$status){
		global $extras_class;
		$services_list_array;

		$response_array = $extras_class->response("false","e1","Hubo un problema interno (500).", "both");
		$query = "SELECT * FROM users WHERE user_id = '$user_id'";
		$result = mysqli_query($this->conn, $query);

		/* Ver si el usuario existe */
		if (mysqli_num_rows($result)) {
		  $query_list = "SELECT * FROM users WHERE type = 'coach'";
		  $result_list = mysqli_query($this->conn, $query_list);

		  if (mysqli_num_rows($result_list)) {
		  	  while($row = mysqli_fetch_assoc($result_list)) {
		  	  	$services_list_array[] = $row;
		  	  }
		  }
		}

		$response_array = $extras_class->response("true","sc1",$services_list_array, "console");

		return $response_array;
	}


	//Get service details
	public function get_service_details($service_id){
		global $extras_class;
		$services_details_array;

		$response_array = $extras_class->response("false","e1","Hubo un problema interno (500).", "both");

		$query_list = "SELECT * FROM service_list WHERE service_id = '$service_id'";
		$result_list = mysqli_query($this->conn, $query_list);

		if (mysqli_num_rows($result_list)) {
			  while($row = mysqli_fetch_assoc($result_list)) {
			  	$services_details_array[] = $row;
			  }

			  $response_array = $extras_class->response("true","sc1","Informaci??n del servicio obtenida con ??xito", "console");
			  $response_array[] = array("service_data"=>$services_details_array);
		} else {
			$response_array = $extras_class->response("false","e1","No se encontr?? un servicio asociado con ese ID", "both");
		}

		return $response_array;
	}
}
?>