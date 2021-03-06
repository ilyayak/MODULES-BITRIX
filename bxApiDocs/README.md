### 🛑 Проект не поддерживается

# bxApiDocs

## bxApiDocs призван упростить, не самую сладкую, жизнь разработчику 1С-Битрикс

Это сама по себе папка с модулями `/bitrix/modules/`, [1С-Битрикс: Управление сайтом](https://www.1c-bitrix.ru/products/cms/) - **[Бизнес](http://www.1c-bitrix.ru/download/cms.php#tab-php-link)**, но с убранными лишними файлами и папками (не `.php`, без классов методов, констант и т.д.), добавленными константами, событиями и хелпами phpDocs.

## Что умеет

Сам по себе ничего не умеет, но содержит несколько крупных вкусностей, что в комплекте с IDE колоcально облегчает жизнь разработчику (расхолаживает, обленяет и т.д. - так что *будьте осторожны*).

### Фичи

* **В общем-то само API**, со всеми [phpDocs](http://ru.wikipedia.org/wiki/PHPDoc)’ами, что удалось автоматически вытащить с [ресурса](http://dev.1c-bitrix.ru/api_help/) официальной документации.
* **Есть даже константы**. Но хелпы есть только у тех, что можно найти на вышеприведенном ресурсе официальной документации.
* **События модулей**. Синтетические классы с набором методов, т.е. контейнер с возможными событиями определенного модуля, все также с документациями.

Документации с примерами использования и ссылками на [ресурс](http://dev.1c-bitrix.ru/api_help/) официальной документации. Константы и события находятся в соответствующих модулях в файлах `bx_events.php` и `bx_constants.php` (Например `/modules/main/bx_events.php` и `/modules/main/bx_constants.php`). Соответственно константы, употребленные в файлах `bx_constants.php`, в местах иx реального употребления закомментированы.

## Как использовать

Добавляем в индексацию любимого IDE или в Composer и все. Счастье!

### Composer

`composer require matiaspub/bx-api-docs --dev`

Composer скачает все доки в папку vendor, IDE проиндексирует изменения. Вам останется наслаждаться результатом.

### Eclipse

**Обязательно PDT (либо аналог)**. В окне PHP Explorer правой кнопкой по проекту, пункт выпадающего меню `Configure -> Add PHP Support`. Снова правой кнопкой по проекту, пункт меню `Include Path -> Configure Include Path`. В окне либо добавляем во **вкладке Libraries** добавляем папку `modules` (кнопка **Add External Source Folder**), либо во **вкладке Projects** добавляем проект `modules` (кнопка **Add**). Предварительно нужно создать проект на основе папки modules и добавить ему поддержку PHP (`Configure -> Add PHP Support` из первого метода). Используя второй метод, Вы сможете редактировать файлы проекта `modules`, когда, воспользовавшись первым методом, файлы-подсказки `modules` будут read-only.

### PhpStorm

В настройках PHP IDE PhpStorm (`File -> Settings -> Default Settings -> PHP` или `File -> Settings -> Languages & Frameworks -> PHP` ) области `Include Path` нажав на "+" добавляем путь к папке `modules`.

_Примечание: в первом случае добавляется папка `modules` для всех новых проектов, во втором - для текущего проекта._ 

## Брюки превращаются. Брюююки прррревращаются.....

### Основные синглтоны $APPLICATION, $DB, $USER, $USER_FIELD_MANAGER с подсказками

![Синглтоны: $APPLICATION](https://monosnap.com/image/dplrjSLmBXtK3A8Rv3nXJIj6g.png)

### Максимально полные доки по методам и классам

![Доки по методам и классам](https://monosnap.com/image/9oRa5bZj9qbLVeNk3R6NYu44u.png)

### Посмотреть события модуля и почитать как его использовать можно так

![События модуля с доками](https://monosnap.com/image/9pIhjhvYbK56RumvtVfoRgDls.png)

### Константы с доками

![Константы с доками](https://monosnap.com/image/FbBLw677cEfUrOMcuGOjH9j3H.png)

## Да, согласен

Подсказки есть не на все методы и с ошибками, но и без этого есть многое (то что, повторюсь, удалось спарсить в автоматическом режиме с сайта оф.доков на котором порядка не больше чем в API). К тому же у Вас есть шанс поучавствовать во вселенском добре, закоммитив изменения или дополнения в эту ветку.

## Обновления

Обновления происходят только по мажорным версиям главного модуля.

## Контакты

* **twitter**: [matiaspub](https://twitter.com/matiaspub)
* **email**: matiaspub@gmail.com
