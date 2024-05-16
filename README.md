# Hatter

A database fixture tool inspired by [Alice](https://github.com/nelmio/alice) and [Haigha](https://github.com/linkorb/haigha), but better suited for non-symfony or third-party application written in other programming languages.

## Usage

Hatter allows you to write human-friendly YAML files containing the data you want to load into your database for testing and initialization. Have a look at the [example/](example/) directory to get a feel for how to write your own database fixtures.

## Hatter vs Alice

Alice is a great tool, but it depends on Doctrine Entities.
This restricts the usage because it can only work for apps that use Doctrine as their ORM

Hatter on the other hand writes to databases directly. This way you can use it for *any* app, even if it's written in another language, or if you don't have access to the source-code.

Alice is more feature rich, but Hatter supports most common use-cases and features, making it a great alternative.

## Usage

```
git clone https://github.com/linkorb/hatter.git
cd hatter
composer install
bin/console hatter:load example/demo.hatter.yaml
```

## Database connection

Hatter reads the connection string from the environment variable `HATTER_DSN`. 
Instead of setting this environment variable, you can also create a `.env.local` file, which hatter will load during startup.

Example:

```ini
# .env.local
HATTER_DSN=mysql://username:password@somehost/mydatabase
```

## Writing database fixtures as YAML files

Have a look at the [example/](example/) directory for some example hatter files.

The general outline of a hatter file:

```yaml
# my-cms-data.hatter.yaml
tables:
  user:
    columns:
      id:
        type: int
        generator: autoIncrement
    rows:
      bob:
        firstname: Bob
        lastname: "{{ faker.lastName() }}"
        age: "{{ faker.numberBetween(18, 60) }}"
      claire:
        firstname: Claire
        lastname: "{{ faker.lastName() }}"
        age: "{{ faker.numberBetween(18, 60) }}"

  post:
    columns:
      id:
        type: int
        generator: autoIncrement
    rows:
      bob-first-post:
        headline: Hello world!
        author_id: @user.bob.id
        content: |
          This is my first beautiful post      
```

Loading this file through hatter will:

1. create two records in the `user` table (for bob and for claire)
2. create a record in the `post` table
3. link the post to user bob (by it's auto generated id)
4. tell hatter to auto generate id values for both tables
5. create random lastnames for bob and claire using the faker library, and assign random ages (note: the random seed is fixed, so the values will be the same on each run)

## Features:

* Use an `includes` key to include one or more external `.hatter.yaml` files to nicely structure your fixture data (wildcard includes supported!)
* Use the [Faker PHP](https://fakerphp.github.io/) library to generate random values
* Use the Symfony Expression language to generate complex values based on referenced fields in other columns, custom functions and many more
* Support generated fields with generators `autoIncrement`, `uuid.v4` and `xuid`

## FAQ and Best Practices

##### Q: Where do I store my hatter files?

A: It's recommended to store your `.hatter.yaml` files in a `hatter/` sub-directory in your application's repository. This way your application, database schema and database fixtures can evolve together. 
For special cases, i.e. dedicated testing projects, it could make sense to store your hatter files in an external dedicated repository for that project and app combination. This also makes sense if you're writing hatter files for a third-party application.

##### Q: How do I name a dedicated Hatter repository?

A: It's recommended to name your repository like `{{ project_name }}-hatter`, i.e. `wordpress-hatter`. The `project_name` generally matches the repository name of the application. For dedicated testing projects a dedicated project name could make more sense (i.e. `wordpress-customer-x-hatter` for a dedicated customer project).

##### Q: What database backends are supported?

A: Database connection strings (DSN) are parsed using the [linkorb/connector](https://github.com/linkorb/connector) library. This library currently supports mysql, pgsql, sqlite and sqlsrv drivers.

##### Q: How do I deal with UUIDs (or XUIDs)

A: Hatter supports auto-generating UUIDs and XUIDs for your database rows. But they will always be random, and different on every run of Hatter. This may not always be desirable.

Some applications / database schemas heavily rely on UUID or similar identifiers. Having these change between Hatter runs can complicate testing.. i.e. it's helpful to have stable IDs to keep testing and itterating on business objects. For this reason it's recommended to generate those IDs externally i.e. using [uuidgenerator.net](https://www.uuidgenerator.net/version4), and paste these values into your YAML file. This way you are sure that the data is restored in the same way on each Hatter run.

To better recognize UUIDs in your test projects, you can consider setting up a format for your UUIDs that aid humans (developers, testers) in recognizing them. For example, your app can use `xxxxxxxx-xxxx-xxxx-xxxx-` as the prefix of all your UUIDs (assuming the underlying database column accepts regular strings). This way you recognize that these are test UUIDs. You can further scope your UUIDs by including test-case names or user names into your UUIDs so you can quickly recognize where given records belong to.


## License

MIT (see [LICENSE.md](LICENSE.md))

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!

