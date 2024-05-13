# Hatter

A database fixture tool inspired by [Alice](https://github.com/nelmio/alice) and [Haigha](https://github.com/linkorb/haigha)

## Usage

Hatter allows you to write human-friendly YAML files containing the data you want to load into your database for testing and initialization.

## VS Alice

Alice is a great tool, but it depends on doctrine entities.
This restricts the usage because it can only work for apps that use Doctrine as their ORM

Hatter on the other hand writes to databases directly. This way you can use it for any app, even if it's written in another language, or if you don't have access to the source-code.

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

Have a look at the `example/` directory for some example hatter files

## License

MIT (see [LICENSE.md](LICENSE.md))

## Brought to you by the LinkORB Engineering team

<img src="http://www.linkorb.com/d/meta/tier1/images/linkorbengineering-logo.png" width="200px" /><br />
Check out our other projects at [linkorb.com/engineering](http://www.linkorb.com/engineering).

Btw, we're hiring!

