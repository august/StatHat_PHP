# StatHat PHP Class #

A PHP class that publishes data to [StatHat](http://stathat.com/), based, in part, on StatHat's own `stathat.php`.

The class includes support for transactions, allowing you to post multiple stats with a single `POST`. If you are publishing several stats at a time, this is more efficient and consumes fewer server resources.

The StatHat class is meant to be used statically. This allows a little more flexibility than creating an actual object. If your site has multiple includes (as most do), you can use a single transaction across all the files. 

For example, you could start the transaction in `header.php`, add stats across the rest of your includes, then commit the transaction in `footer.php`.

## Usage ##
Usage is easy.

	// set the ez key
	StatHat::setEZKey('xxxxxxxxxx');
	
	// start the transaction
	StatHat::beginTransaction();
	
	// post a count stat
	StatHat::publishCount('page views', 1);
	
	// post a count stat using a timestamp
	StatHat::publishCount('page views', 1, time());

	// post a value stat
	StatHat::publishValue('server load', 1.1);
	
	// post a value stat using a timestamp
	StatHat::publishValue('server load', 1.2, time());
	
	// commit the transaction
	// this posts all stats to the server
	StatHat::commitTransaction();
	
Transactions are optional. If you don't `StatHat::startTransaction()`, all stats are posted immediately.

The StatHat class supports both synchronous and asynchronous posting of your stats. The default is to post them asynchronously. 

	// post a count stat synchronously
	$result = StatHat::publishCountSynchronous('page views',1);

If you're using transactions, you can post the whole set synchronously as well.

	// post transaction synchronously
	$result = StatHat::commitTransactionSynchronous();

	