<?php
// external-script.php

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data sent via POST and sanitize
    $user_full_name = isset($_POST['user_full_name']) ? sanitize_data($_POST['user_full_name']) : '';
    $billing_address = isset($_POST['billing_address']) ? sanitize_data($_POST['billing_address']) : '';

    // Check if both the full name and billing address are provided
    if (!empty($user_full_name) && !empty($billing_address)) {
        // Process the user data as needed

        $credit_safe_username = 'jwienczkowski@generalequip.com';
		$credit_safe_password = '3tidvEgXB6H#DVm(MEQGa%';

		// Define the API base URL for the sandbox environment
		$baseURL = 'https://connect.creditsafe.com/v1';

		function getCompanyIdFromAPI($credit_safe_username, $credit_safe_password, $user_full_name, $billing_address) {
			global $baseURL;

			// Endpoint to authenticate and obtain an authentication token
			$authEndpoint = $baseURL . '/authenticate';

			// Create an array with the username and password for the POST request
			$data = array(
				'username' => $credit_safe_username,
				'password' => $credit_safe_password
			);

			// Initialize cURL session for authentication
			$ch = curl_init($authEndpoint);

			// Set cURL options for the POST request
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Execute the POST request
			$response_api = curl_exec($ch);

			// Check for cURL errors
			if (curl_errno($ch)) {
				echo 'Error: ' . curl_error($ch);
			} else {
				// Decode the JSON response
				$responseData = json_decode($response_api, true);

				// Check if the authentication was successful
				if (isset($responseData['token'])) {
					// Extract the authentication token
					$authToken = $responseData['token'];

					// Define the search criteria
					$searchCriteria = array(
						'name' => $user_full_name, // Replace with the actual company name
						'countries' => 'US', // Replace with the desired ISO-2 country code
						'street' => $billing_address
					);

					// Build the query string with search criteria
					$queryString = http_build_query($searchCriteria);

					// Endpoint to search for companies
					$searchEndpoint = $baseURL . '/companies?' . $queryString;

					// Initialize cURL session for company search
					$ch = curl_init($searchEndpoint);

					// Set cURL options for the GET request with authorization
					$headers = array(
						'Authorization: Bearer ' . $authToken
					);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					// Execute the GET request for company search
					$companySearchResponse = curl_exec($ch);

					// Close the cURL session for company search
					curl_close($ch);

					// Decode the company search response
					$searchData = json_decode($companySearchResponse, true);

					// Check if the response contains company data
					if (isset($searchData["companies"][0]["id"])) {
						return $searchData["companies"][0]["id"];
					} else {
					    //return $user_full_name;
					    //return $billing_address;
						return null; // No ID found in the response
					}
				} else {
					// Authentication failed
					echo 'Authentication failed. Error: ' . $responseData['error'];
				}
			}

			// Close the cURL session for authentication
			curl_close($ch);
		}



		// Function to retrieve the Company Credit Report
		function getCompanyReport($connectId, $credit_safe_username, $credit_safe_password) {
			global $baseURL;

			// Endpoint to authenticate and obtain an authentication token
			$authEndpoint = $baseURL . '/authenticate';

			// Create an array with the username and password for the POST request
			$data = array(
				'username' => $credit_safe_username,
				'password' => $credit_safe_password
			);

			// Initialize cURL session for authentication
			$ch = curl_init($authEndpoint);

			// Set cURL options for the POST request
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Execute the POST request
			$response_api = curl_exec($ch);

			// Check for cURL errors
			if (curl_errno($ch)) {
				echo 'Error: ' . curl_error($ch);
				return false;
			} else {
				// Decode the JSON response
				$responseData = json_decode($response_api, true);

				// Check if the authentication was successful
				if (isset($responseData['token'])) {
					// Extract the authentication token
					$authToken = $responseData['token'];

					// Define optional query parameters
					$queryParams = array(
						'language' => 'en', // Report language (optional)
						'template' => 'full', // Template (optional)
					);

					// Build the query string with optional parameters
					$queryString = http_build_query($queryParams);

					// Endpoint to retrieve the Company Credit Report
					$reportEndpoint = $baseURL . '/companies/' . $connectId . '?' . $queryString;

					// Initialize cURL session for report retrieval
					$ch = curl_init($reportEndpoint);

					// Set cURL options for the GET request with authorization
					$headers = array(
						'Authorization: Bearer ' . $authToken,
						'Accept: application/json+pdf' // Request PDF format
					);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

					// Execute the GET request for report retrieval
					$companyReport = curl_exec($ch);

					// Close the cURL session for report retrieval
					curl_close($ch);

					return $companyReport;
				} else {
					// Authentication failed
					echo 'Authentication failed. Error: ' . $responseData['error'];
					return false;
				}
			}

			// Close the cURL session for authentication
			curl_close($ch);
		}
		
		// Call the function to get the company ID
		$companyId = getCompanyIdFromAPI($credit_safe_username,$credit_safe_password,$user_full_name,$billing_address);

		// Define the connectId for the specific company report you want to retrieve
		$connectId = $companyId; // Replace with the actual connectId
         

		// Call the function to get the company report
		$companyReport = getCompanyReport($connectId,$credit_safe_username,$credit_safe_password);

		if ($companyReport !== false) {
			// Handle the company report as needed
			// echo 'Company Credit Report: ' . $companyReport;
			$data = json_decode($companyReport, true);

			if (isset($data["report"]["companySummary"]["creditRating"]["providerValue"]["value"])) {
				$creditscore =  $data["report"]["companySummary"]["creditRating"]["providerValue"]["value"];

				if ($creditscore >= 51) {
                    $response = 1;

                    // Send a response back to the AJAX request
                    echo $response;
				} else {
                    
					$response = 0;

                    // Send a response back to the AJAX request
                    echo $response;
				}

			} else {
				  $notfound = 'Not found';
				   
				   echo $notfound;
				// Debugging the decoded data
				// echo '<pre>';
				// var_dump($data);
				// echo '</pre>';
			}

		}
    } else {
        // Handle the case where either the full name or billing address is missing
        http_response_code(400); // Bad Request
        echo 'Missing user data. Both full name and billing address are required.';
    }
} else {
    // Handle non-POST requests
    http_response_code(405); // Method Not Allowed
    echo 'This script only accepts POST requests.';
}

function sanitize_data($data) {
    // Implement your data sanitization logic here
    // Example: Remove HTML tags and trim whitespace
    $sanitized_data = trim(strip_tags($data));
    return $sanitized_data;
}


?>
