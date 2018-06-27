# Own my money!

Own my money is a simple way to track your bank activity on any device connected to the web (pc, smartphone, tablet, ...).
It can work as a standalone application or in your smartphone browser.

You can manage your account with the OFX or CSV integration.

## Installation and usage

Following example is based on Linux distribution with Apache :

1. Download the latest version [here](https://github.com/nioc/own-my-money/archive/master.tar.gz)
2. Untar the archive : `tar -xvzf own-my-money-master.tar.gz`
3. Move the files into you web server directory `mv own-my-money-master /var/www/money`
4. Fix file permissions `chown www-data:www-data /var/www/money -R`
5. Create database stuff using with the [database setup page](http://localhost/server/configuration/setup.php)
6. Update Admin user (edit password obviously)
7. Remove unnecessary files (/docs, /configuration/setup.php, ...)
8. Set your library folder

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/nioc/own-my-money/tags).

## Authors

* **[Nioc](https://github.com/nioc/)** - *Initial work*

See also the list of [contributors](https://github.com/nioc/own-my-money/contributors) who participated in this project.

## Motivation

Our aim is to provide a self-hosted manager for personal finances.

## Contributing

The project is open and any contribution is welcome!

To keep the code clean, we use [php-cs-fixer](http://cs.sensiolabs.org/), before commit launch this on each edited files:

```` bash
php /usr/local/bin/php-cs-fixer fix /path/to/editedFile.php -v
````
You can handle all edited files with this single line:
```` bash
cd /var/www/money/server; for file in $(git diff-index --name-only HEAD); do php /usr/local/bin/php-cs-fixer fix "$file" -v; done
````

A little how-to for github:

1. [Fork it](https://help.github.com/articles/fork-a-repo/)
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes (with a detailled message): `git commit -am 'Add an awesome feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/dcddd6d1c1284ea496b9a1015e775b2d)](https://www.codacy.com/app/nioc/own-my-money)

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE.md) file for details

## Included project

This project includes the following:
- [VueJS](https://vuejs.org/)
- [Bootstrap](https://getbootstrap.com/)
- [Bootstrap Vue](https://bootstrap-vue.js.org/)
- [Vue-moment](https://github.com/brockpetrie/vue-moment)
- [Vue-resource](https://github.com/pagekit/vue-resource)
- [Datepicker](https://github.com/charliekassel/vuejs-datepicker)
- [vue-chartjs](https://vue-chartjs.org/)
- [Chart.js](https://www.chartjs.org/)
- [accounting.js](https://github.com/openexchangerates/accounting.js)
- [Font Awesome](https://github.com/FortAwesome/Font-Awesome/)
