The files in the folder contains the code for wordpress hooks that gets the current logged-in user's details such as first name, last name, address, and role. The user information is used to generate JavaScript variables that can be passed to an external script via AJAX.

The external script comprises PHP code that interfaces with the Credit Safe API to retrieve the credit score of the company. This retrieval occurs after being triggered by the AJAX code within the WordPress hook. The PHP code utilizes the first name, last name, and address of the company that was sent to assess the user's credit. Based on this assessment, it offers a notification indicating whether the user qualifies or not.




