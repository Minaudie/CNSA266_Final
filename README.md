# CNSA266_Final
Final project for CNSA 266 - Online Auction Site

Miranda Dorosz and Sam Leonard

Images for items are saved on the IIS/SQL server. I have not included them in the repository.

** NOTE **
This project has been reconfigured (from the presentation) to localhost.
The proxy IP address has been commented out in config.php, which is where this is set.

Also, I fixed the problem where the edit page was not working correctly on submission.
Since the price input is disabled if there are bids, I think it is not POSTing.
Therefore, the page was looking for $_POST['price'] that did not exist.
I added a hidden form field for price so it goes into POST correctly. I also added
an if/else statement so that if bids exist, it does not change the starting price
in the database.

I have tested it with items with bids and items without bids and it is working
as expected.
