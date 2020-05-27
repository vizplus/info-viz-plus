<?php
$ltmp_arr=array(
	'ops-history'=>array(
		'award_to'=>'Награждение <a class="view-account" href="/accounts/%receiver%/">%receiver%</a> на <span class="view-percent">%energy%%</span>',
		'award_from'=>'Получена награда от <a class="view-account" href="/accounts/%initiator%/">%initiator%</a> на <span class="view-percent">%energy%%</span>',
		'award_memo'=>' с заметкой ',
		'benefactor_award'=>'Получена бенефициарская награда <span class="view-tokens">%shares%</span> от <a class="view-account" href="/accounts/%initiator%/">%initiator%</a>',
		'benefactor_award_empty'=>'Получена пустая бенефициарская награда от <a class="view-account" href="/accounts/%initiator%/">%initiator%</a>',
		'receive_award'=>'<span class="view-tokens">%shares%</span> получено в награду от <a class="view-account" href="/accounts/%initiator%/">%initiator%</a>',
		'account_metadata'=>'Обновлен публичный профиль',
		'account_create'=>'Создан аккаунт <a class="view-account" href="/accounts/%account%/">%account%</a>',
		'account_create_tokens'=>' с передачей в социальный капитал <span class="view-tokens">%tokens%</span>',
		'account_create_delegation'=>', делегировано <span class="view-tokens">%shares%</span>',
		'create_invite'=>'Выписан чек на <span class="view-tokens">%tokens%</span> с кодом проверки <span class="view-key">%key%</span>',
		'claim_invite_balance'=>'Погашен чек с кодом <span class="view-key">%key%</span>',
		'use_invite_balance'=>'Погашен чек с кодом <span class="view-key">%key%</span>',
		'transfer_from'=>'<span class="view-tokens">%tokens%</span> отправлено <a class="view-account" href="/accounts/%to%/">%to%</a>',
		'transfer_to'=>'<span class="view-tokens">%tokens%</span> получено от <a class="view-account" href="/accounts/%from%/">%from%</a>',
		'transfer_memo'=>' с заметкой ',
		'transfer_to_vesting_from'=>'<span class="view-tokens">%tokens%</span> отправлено в социальный капитал <a class="view-account" href="/accounts/%to%/">%to%</a>',
		'transfer_to_vesting_to'=>'<span class="view-tokens">%tokens%</span> получено в социальный капитал от <a class="view-account" href="/accounts/%from%/">%from%</a>',
		'withdraw_vesting'=>'Начато уменьшение социального капитала на <span class="view-tokens">%shares%</span>',
		'withdraw_vesting_stop'=>'Остановлено уменьшение социального капитала',
		'fill_vesting_withdraw'=>'<span class="view-tokens">%tokens%</span> получено от уменьшения социального капитала',
		'fill_vesting_withdraw_from'=>'Аккаунту <a class="view-account" href="/accounts/%to%/">%to%</a> отправлено <span class="view-tokens">%tokens%</span> от понижения социального капитала',
		'fill_vesting_withdraw_to'=>'<span class="view-tokens">%tokens%</span> получено от уменьшения социального капитала аккаунтом <a class="view-account" href="/accounts/%from%/">%from%<a>',
		'delegate_vesting_shares_from'=>'<span class="view-tokens">%shares%</span> получено делегированием от <a class="view-account" href="/accounts/%from%/">%from%</a>',
		'delegate_vesting_shares_to'=>'<span class="view-tokens">%shares%</span> делегировано аккаунту <a class="view-account" href="/accounts/%to%/">%to%</a>',
		'delegate_vesting_shares_from_stop'=>'Отмена делегирования от <a class="view-account" href="/accounts/%from%/">%from%</a>',
		'delegate_vesting_shares_to_stop'=>'Отмена делегирования для <a class="view-account" href="/accounts/%to%/">%to%</a>',
		'return_vesting_delegation'=>'<span class="view-tokens">%shares%</span> возвращено в социальный капитал из делегирования',


		'committee_worker_create_request'=>'Создание заявки в фонд ДАО, воркер <a class="view-account" href="/accounts/%worker%/">%worker%</a>, запрашиваемая сумма <span class="view-tokens">%tokens%</span>, длительность <span class="view-date">%days% дней</span>, <span class="view-memo" data-text="%text%">информация</span>',
		'committee_worker_cancel_request'=>'Отмена заявки №%request_id% в фоне ДАО',
		'committee_vote_request'=>'Голос на <span class="view-percent">%percent%%</span> по заявке №%request_id% в фонд ДАО',
		'committee_pay_request'=>'Выплата <span class="view-tokens">%tokens%</span> из фонда ДАО по заявке  №%request_id%',

		'account_update'=>'Обновление прав доступа',

		'set_subaccount_price'=>'Выставление на продажу субаккаунтов за <span class="view-tokens">%tokens%</span>, бенефициар <a class="view-account" href="/accounts/%seller%/">%seller%</a>',
		'set_subaccount_price_stop'=>'Остановка продажи субаккаунтов',
		'set_account_price'=>'Аккаунт <a class="view-account" href="/accounts/%account%/">%account%</a> выставлен на продажу за <span class="view-tokens">%tokens%</span>, бенефициар <a class="view-account" href="/accounts/%seller%/">%seller%</a>',
		'set_account_price_stop'=>'Снятие аккаунта с продажи',
		'account_sale'=>'<span class="view-tokens">%tokens%</span> получено от продажи аккаунта <a class="view-account" href="/accounts/%account%/">%account%</a>. Покупатель: <a class="view-account" href="/accounts/%buyer%/">%buyer%</a>',
		'buy_account'=>'Покупка аккаунта <a class="view-account" href="/accounts/%account%/">%account%</a> за <span class="view-tokens">%tokens%</span>, переведено в капитал <span class="view-tokens">%tokens_to_shares%</span>',

		'shutdown_witness'=>'Отключение делегата',
		'witness_update'=>'Включение делегата, ключ подписи <span class="view-key">%key%</span>, <span class="view-memo" data-text="%text%">информация</span>',
		'witness_update_stop'=>'Отключение делегата',

		'account_witness_vote'=>'Голос за делегата <a class="view-account" href="/witnesses/%witness%/">%witness%</a>',
		'account_witness_vote_stop'=>'Снят голос с делегата <a class="view-account" href="/witnesses/%witness%/">%witness%</a>',

		'account_witness_vote_income'=>'<a class="view-account" href="/accounts/%account%/">%account%</a> проголосовал за делегата',
		'account_witness_vote_income_stop'=>'<a class="view-account" href="/accounts/%account%/">%account%</a> снял голос с делегата',
		'paid_subscription_action_to'=>'Оплата подписки на <a class="view-account" href="/accounts/%account%/">%account%</a>, уровень %level%, стоимость подписки <span class="view-tokens">%summary_amount%</span>',
		'paid_subscription_action_from'=>'Оплата подписки от <a class="view-account" href="/accounts/%subscriber%/">%subscriber%</a>, уровень %level%, стоимость подписки <span class="view-tokens">%summary_amount%</span>',


		'cancel_paid_subscription_to'=>'Завершение подписки на <a class="view-account" href="/accounts/%account%/">%account%</a>',
		'cancel_paid_subscription_from'=>'Завершение подписки от <a class="view-account" href="/accounts/%subscriber%/">%subscriber%</a>',

		'paid_subscribe_to'=>'Оформлена подписка на <a class="view-account" href="/accounts/%account%/">%account%</a>, уровень %level%, суммарная стоимость подписки <span class="view-tokens">%summary_amount%</span>',
		'paid_subscribe_from'=>'Оформлена подписка от <a class="view-account" href="/accounts/%subscriber%/">%subscriber%</a>, уровень %level%, суммарная стоимость подписки <span class="view-tokens">%summary_amount%</span>',
		'paid_subscribe_auto_renewal'=>' (включено автопродление)',

		'set_paid_subscription'=>'Создание платной подписки, <a href="%link%">условия</a>, максимальный уровень: %levels%, стоимость за уровень: %amount%, период действия подписки (количество дней): %period%',
		'invite_registration'=>'Регистрация по инвайт-коду <span class="view-key">%key%</span>',
	),
);