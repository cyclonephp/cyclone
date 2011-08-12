### Why SimpleDB?

The SimpleDB module aims to replace the query builder of the official Kohana
Database module. It's simpler, more flexible and better designed than the Database
module, and it supports transactions.

### The DB class

The DB class is the core class of SimpleDB. It's "core" because you will have to
use it very often, but in fact it's just a set of static factory methods for other
SimpleDB classes and also provides some helpers.

### Building some simple queries

First of all, look at a query like
<code>SELECT * FROM `users`</code> - nothing
could be more trivial.
Building it with SimpleDB looks like this:
<code>DB::select()->from('users')</code>

Let's add a WHERE part with a parameter: <code>SELECT * FROM `users` WHERE `id` = 2</code>
looks like this:

<code>$id = 2;
DB::select()->from('users')->where('id', '=', DB::esc($id));</code>

The <code>$id</code> parameter should be put into a <code>DB::esc()</code> call
 - it's a significant difference related to the database module. Of course in
real usage the <code>$id</code> parameter wouldn't be hard-coded. It surely comes
from an untrused source - route or query parameter, etc. Escaping goes via the
<code>DB::esc()</code> call in SimpleDB, and it's not performed automatically.
More about the details later.

If you want to specify the columns to be selected, it also goes simply:
<code>DB::select('id', 'name')->from('users')->where('id', '=', DB::esc(2));</code>

Now you are able to build simple queries with SimpleDB. Of course in the DB class you can
also find helpers to create UPDATE, INSERT, DELETE queries, but now let's talk
about query execution.

### Executing queries

Basic query execution is very simple. After you build a query, you call it's
<code>exec()</code> method:

<code>DB::select()->from('user')->where('id', '=', DB::esc($id))->exec();</code>

The return value of the <code>exec()</code> call is up to the type of the query.
In the case of a SELECT query its a <code>DB_Query_Result</code> instance, which
is a flexible iterator, otherwise it's the number of the affected rows.

### Working with query results

The <code>DB_Query_Result</code> abstract class is the base class for all the
adapter-specific query result iterators. It has got two useful methods:

- <code>rows($type)</code>: all rows returned by the query will be an instance of
the <code>$type</code> class.

- <code>index_by($column)</code>: the rows will be indexed by the <code>$column</code>
column.

Example:

    $users = DB::select()->from('users')->where('role', '=', 'admin')->exec()
        ->rows('Record_User')->index_by('id');

    foreach ($users as $id => $user) {
        echo $id.' : '.$user->name;
    }
