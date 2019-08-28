# AVE.cms v3.26
###### Многофункциональная, система управления сайтом.

## Оглавление

* [Системные требования](#Системные-требования)

* [Установка](#Установка)

* [Ресурсы](#Ресурсы)


## Системные требования

 * Операционная система: 
   * Unix 
   * Windows Server

 * ПО WEB сервера: 
   * Apache >= 1.3
   * Nginx >= 1.6.2
   * PHP >= 5.6.x (zlib, cURL, mbString, JSON)
   * PHP >= 7.x ... <= 7.2.x
   * MySQL >= 5.6.x ... <= 5.7.x

## Установка

1. Распакуйте содержимое архива в новую папку на вашем локальном компьютере.
2. Загрузить эту папку целиком через FTP-клиент на ваш хост.
3. Вам также может потребоваться установить права доступа (CHMOD 777) рекурсивно, на папки /tmp/cache/, /tmp/session/ и /uploads/, если ваш хостинг не устанавливает это по умолчанию.
4. Также вам может потребоваться установить права доступа (CHMOD 777) рекурсивно, на файлы inc/db.config.php и inc/config.inc.php, если ваш хостинг не устанавливает это по умолчанию.
5. Наберите http://адрес вашего сайта/ в браузере.
6. Следуйте инструкциям.
7. После установки системы настоятельно рекомендуем устанавливать права доступа на папку uploads не выше CHMOD 755. 

## MySQL >= 5.7
Cекция [mysqld]
 * sql_mode = "NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"

## Ресурсы

Сайт: [ave-cms.ru](https://ave-cms.ru/)

Форум: [forum](https://www.nulled.cc/threads/102658/)

E-mail: support@ave-cms.ru

Google+: [Google+](https://plus.google.com/106406255345948508717)

Вконтакте: [vk.com/ave_cms](http://vk.com/ave_cms)


---
Copyright © 2007-2019 [Ave-Cms.Ru](https://ave-cms.ru) | [AVE.cms 3.26](https://ave-cms.ru)