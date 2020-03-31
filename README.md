# info.VIZ.plus

Перед вами открытый код обозревателя блоков info.viz.plus. Он содержит несколько процессов, которые собирают, обрабатывают, связывают данные из блокчейна VIZ.

## Процессы

- waterfall_irreversible — сбор блоков, транзакций и операций по необратимому блоку VIZ;
- ops_processing — обработка операций, заполнение базы данных, триггеры для обновления аккаунтов и делегатов;
- ops_linking — связывание операций с аккаунтами для построения истории действий;
- ops_working — эмуляция работы отдельных операций для получения актуального слепка набора данных (delegations, escrow);
- updater — обновление данных аккаунтов и делегатов;
- props_snapshot — получение слепков динамического глобального объекта состояния и параметров системы (очереди делегатов);
- snapshot — слепки пользователей, делегатов, статистики системы;
- backup — создание отдельных бекапов для каждой значимой таблицы базы данных;

## Модули

- index — главная страница со статистикой, параметры системы и динамика их изменений;
- accounts — таблица пользователей, обзор профилей, истории значимых операций;
- witnesses — таблица делегатов, фильтрация по выставленным параметрам, обзор делегата и голосов за него;
- explorer — поиск по номеру блока, хеш-сумме блока или транзакции, очередь делегатов;

## Дополнения

В каталоге share содержатся примеры скриптов для перезапуска необходимых процессов, предустановка crontab, начальная структура таблиц (SQL).

В каталоге backup содержатся архивы ключевых таблиц на конец марта 2020 года.

## Зависимости

- MySQL/MariaDB;
- nginx;
- php;
- jsonrpc нода VIZ;
- jQuery;
- pretty-print-json.js;
- sortable.js;
- highcharts.js;
