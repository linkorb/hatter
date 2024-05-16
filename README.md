# Hatter

A database fixture tool inspired by [Alice](https://github.com/nelmio/alice) and [Haigha](https://github.com/linkorb/haigha), but better suited for non-symfony or third-party application written in other programming languages.

## Usage

Hatter allows you to write human-friendly YAML files containing the data you want to load into your database for testing and initialization. Have a look at the [examples/ directory](examples/) to get a feel for how to write your own database fixtures.

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

Have a look at the `example/` directory for some example hatter files.

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

## License

MIT (see [LICENSE.md](LICENSE.md))

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!

