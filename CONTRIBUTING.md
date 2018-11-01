## Contributing

The project is open and any contribution is welcome!

#### Backend part (PHP)

To keep the code clean, we use [php-cs-fixer](http://cs.sensiolabs.org/), before commit launch this on each edited files:

```` bash
php /usr/local/bin/php-cs-fixer fix /path/to/editedFile.php -v
````
You can handle all edited files with this single line:
```` bash
cd /var/www/money; for file in $(git diff-index --name-only HEAD); do php /usr/local/bin/php-cs-fixer fix "$file" -v; done
````

#### Frontend part (VueJS)

In order to contribute to the VueJS frontend:

0. Install prerequisite:
  - [Node.js](https://nodejs.org/)
  - npm `npm install npm@latest -g`
  - Vue.js `npm install -g vue,`
  - Vue-cli `npm install -g vue-cli,`
1. Access the frontend folder in a shell `cd /var/www/money/money-front-vue`
2. Build the project `npm install` and wait for the downloads
3. Start the node server `npm run dev`
4. Edit the code!

#### A little how-to for Github

1. [Fork it](https://help.github.com/articles/fork-a-repo/)
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes (with a detailled message): `git commit -am 'Add an awesome feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request
