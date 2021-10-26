<?php
$ltmp_preset['en']=[
	'active'=>true,
	'meta'=>[
		'description'=>'All actual information and statistics on the VIZ blockchain: block explorer, delegates, accounts',
	],
	'menu'=>[
		'accounts'=>'Accounts',
		'witnesses'=>'Witnesses',
		'explorer'=>'Explorer',
	],
	'explorer'=>[
		'title'=>'Block explorer',
		'description'=>'Summary data in the VIZ blockchain, block overview, transactions, witness schedule, global system properties.',

		'last_tx_caption'=>'Latest transactions',
		'last_blocks_caption'=>'Latest blocks',

		'search_title'=>'Search',
		'search_description'=>'Search for blocks and transactions by hash in the VIZ blockchain.',
		'search_caption'=>'Search',
		'search_placeholder'=>'Search by block number, transaction hash',
		'search_none'=>'The query "{query}" did not find anything. Try again.',

		'blocks_title'=>'Blocks',
		'blocks_description'=>'An overview of the latest blocks in the VIZ blockchain system, search by number and hash.',
		'blocks_caption'=>'Block',
		'blocks_data'=>'Block {id} sequenced {date} and signed by {witness}. Contains {trx} transactions, {ops} operations, {vops} virtual operations.',
		'blocks_hash'=>'Hash:',
		'blocks_datetime'=>'Signed datetime:',
		'blocks_witness'=>'Signed by witness:',

		'tx_title'=>'Transactions',
		'tx_description'=>'Overview of recent VIZ blockchain transactions, search by hash.',
		'tx_caption'=>'Transaction',
		'tx_data'=>'Transaction {hash} in {block} VIZ block. Contains {ops} operations, {vops} virtual operations.',
		'tx_block'=>'Block:',
		'tx_op_virtual'=>'Virtual',

		'tx_table_caption'=>'Transactions',
		'tx_table_datetime'=>'Datetime (GMT)',
		'tx_table_witness'=>'Witness',
		'tx_table_trx'=>'Transactions',
		'tx_table_num'=>'Number',
		'tx_table_hash'=>'Hash',
		'tx_table_ops'=>'Operations',
		'tx_table_vops'=>'Virt. operations',

		'tx_ops_caption'=>'Operations',
		'tx_op_type'=>'Operation type',
		'tx_op_json'=>'JSON',
		'tx_vops_caption'=>'Virtual operations',

		'schedule_title'=>'Witness schedule',
		'schedule_datetime'=>'Schedule shuffle datetime:',
		'schedule_shuffle_block'=>'Schedule shuffle block number:',
		'schedule_table_num'=>'Number',
		'schedule_table_target_witness'=>'Witness',
		'schedule_table_witness'=>'Actual witness',
	],
	'props_descr'=>[
		'account_creation_fee'=>'Account creation fee',
		'create_account_delegation_ratio'=>'Account creation cost by delegating',
		'create_account_delegation_time'=>'Delegation period when creating an account',
		'bandwidth_reserve_percent'=>'Bandwidth reserve for microaccounts',
		'bandwidth_reserve_below'=>'Maximum capital of a micro account',
		'maximum_block_size'=>'Maximum block size',
		'data_operations_cost_additional_bandwidth'=>'Additional bandwidth surcharge for each data operation in a transaction',
		'min_delegation'=>'Minimum number of tokens for delegation',
		'vote_accounting_min_rshares'=>'Minimum amount of award capital',
		'committee_request_approve_min_percent'=>'Minimum share of total social capital for a decision on request in the DAO Fund',
		'witness_miss_penalty_percent'=>'Penalty to a witness for missing a block (% of the total weight of votes for a witness)',
		'witness_miss_penalty_duration'=>'Duration of the penalty to the witness for skipping the block',
		'inflation_witness_percent'=>'Share of the emission going to the reward of the witnesses',
		'inflation_ratio_committee_vs_reward_fund_short'=>'Share of the remaining emission going to the DAO Fund',
		'inflation_ratio_committee_vs_reward_fund'=>'Share of the remaining emission going to the DAO Fund (the rest to the Reward Fund)',
		'inflation_recalc_period'=>'Period of fixation of the emission model',

		'create_invite_min_balance'=>'Minimum check amount',
		'committee_create_request_fee'=>'Fee for creating request to the DAO Fund',
		'create_paid_subscription_fee'=>'Fee for creating a paid subscription',
		'account_on_sale_fee'=>'Fee for putting an account on sale',
		'subaccount_on_sale_fee'=>'Fee for putting subaccounts on sale',
		'witness_declaration_fee'=>'Fee for declaring an account as a witness',
		'withdraw_intervals'=>'Number of periods (days) of capital withdraw',
	],
	'props_descr_type'=>[
		'account_creation_fee'=>'viz',
		'create_account_delegation_ratio'=>'delegated viz',
		'create_account_delegation_time'=>'days',
		'bandwidth_reserve_percent'=>'%',
		'bandwidth_reserve_below'=>'viz',
		'maximum_block_size'=>'byte',
		'data_operations_cost_additional_bandwidth'=>'%',
		'min_delegation'=>'viz',
		'vote_accounting_min_rshares'=>'viz',
		'committee_request_approve_min_percent'=>'%',
		'witness_miss_penalty_duration'=>'days',

		'create_invite_min_balance'=>'viz',
		'committee_create_request_fee'=>'viz',
		'create_paid_subscription_fee'=>'viz',
		'account_on_sale_fee'=>'viz',
		'subaccount_on_sale_fee'=>'viz',
		'witness_declaration_fee'=>'viz',
	],
	'props_item'=>[
		'account_creation_fee'=>'viz',
		'create_account_delegation_ratio'=>'viz',
		'create_account_delegation_time'=>'days',
		'bandwidth_reserve_percent'=>'%',
		'bandwidth_reserve_below'=>'viz',
		'maximum_block_size'=>'byte',
		'data_operations_cost_additional_bandwidth'=>'%',
		'min_delegation'=>'viz',
		'vote_accounting_min_rshares'=>'viz',
		'committee_request_approve_min_percent'=>'%',
		'witness_miss_penalty_percent'=>'%',
		'witness_miss_penalty_duration'=>'days',
		'inflation_recalc_period'=>'days',
		'inflation_witness_percent'=>'%',
		'inflation_ratio_committee_vs_reward_fund'=>'%',

		'create_invite_min_balance'=>'viz',
		'committee_create_request_fee'=>'viz',
		'create_paid_subscription_fee'=>'viz',
		'account_on_sale_fee'=>'viz',
		'subaccount_on_sale_fee'=>'viz',
		'witness_declaration_fee'=>'viz',
		'withdraw_intervals'=>'',
	],
	'witnesses'=>[
		'title'=>'Witnesses',
		'description'=>'VIZ blockchain witnesses table',
		'personal_description'=>'Statistics and information about the witness {witness} in the VIZ blockchain',
		'inactive'=>'Inactive',
		'addon_col'=>'Additional column:',
		'col_penalty'=>'Penalty',
		'col_blocks'=>'Blocks',
		'col_total_missed'=>'Misses',
		'col_rewards'=>'Reward',
		'col_prop'=>'Property:',

		'witness'=>'Witness',
		'votes'=>'Votes weight',
		'votes_descr'=>'Summary transferable weight of votes from members of the network',
		'version'=>'Version',
		'version_descr'=>'Protocol version used',
		'penalty'=>'Penalty',
		'penalty_descr'=>'Penalty for missing blocks affects the weight of votes counted',
		'blocks'=>'Blocks',
		'blocks_descr'=>'Number of signed blocks',
		'total_missed'=>'Misses',
		'total_missed_descr'=>'Number of missed blocks',
		'rewards'=>'Reward',
		'rewards_descr'=>'The amount of rewards received for signing blocks',
		'prop'=>'Property',

		'url'=>'Witness statement',
		'key'=>'Signing key',
		'status'=>'Status',
		'status_disabled'=>'disabled',
		'sum_votes'=>'Summary weight of votes received',
		'voter_list'=>'List of voters',
		'vote_weight'=>'vote weight:',
		'voted_props'=>'Voting properties',
		'emission_distribution'=>'Distribution of the emission',
		'reward_fund'=>'Reward Fund',
		'dao_fund'=>'DAO Fund',
		'witnesses_fund'=>'Witnesses Fund',
	],
	'accounts'=>[
		'title'=>'Accounts',
		'description'=>'Table of accounts in the VIZ blockchain',
		'personal_description'=>'Information about account {account} in the VIZ blockchain',
		'full_personal_description'=>'Card about account @{account} in the VIZ blockchain: history of operations, social capital, delegation, public keys, votes for witnesses.',
		'account'=>'Account',
		'avatar'=>'Avatar',
		'nickname'=>'Nickname',
		'about'=>'About',
		'empty_about'=>'No account description',
		'location'=>'Location',
		'site'=>'Web-site',
		'mail'=>'E-mail',
		'interests'=>'Interests:',
		'services'=>'Services:',

		'services_facebook'=>'Facebook',
		'services_instagram'=>'Instagram',
		'services_twitter'=>'Twitter',
		'services_vk'=>'VK',
		'services_telegram'=>'Telegram',
		'services_skype'=>'Skype',
		'services_viber'=>'Viber',
		'services_whatsapp'=>'WhatsApp',

		'created'=>'Created:',
		'genesis'=>'genesis',

		'receiver_awards'=>'Received awards:',
		'benefactor_awards'=>'Benefactor awards:',
		'keys_type_1'=>'Master',
		'keys_type_2'=>'Active',
		'keys_type_3'=>'Regular',
		'keys_type_4'=>'Memo',

		'public_keys'=>'Public keys',
		'authority_type'=>'Authority type',
		'public_key'=>'Public key',
		'weight_threshold'=>'Weight / threshold',

		'authority_type_1'=>'Master access',
		'authority_type_2'=>'Active access',
		'authority_type_3'=>'Regular access',

		'delegated_authority'=>'Delegated authority',
		'assets'=>'Assets',
		'capital'=>'Capital (viz)',
		'balance'=>'Balance (viz)',
		'energy'=>'Energy (%)',
		'withdraw_enabled'=>'Withdraw enabled',
		'income_delegation'=>'Incoming delegation',
		'outcome_delegation'=>'Outgoing delegation',
		'delegations'=>'Delegations',
		'delegations_account'=>'Account',
		'delegations_amount'=>'Capital (viz)',
		'delegations_reclaimed'=>'Reclaimed',

		'dao'=>'DAO',
		'dao_witness'=>'Witness:',
		'dao_witness_yes'=>'Yes',
		'dao_witness_no'=>'No',
		'dao_witness_vote_weight'=>'Weight of the vote for a witness:',
		'dao_witness_votes'=>'List of votes for witnesses:',
		'dao_witness_votes_empty'=>'Votes for witnesses: <span class="red">None</span>',

		'history'=>'Operation history',
		'history_types_accounts'=>'Accounts',
		'history_types_capital'=>'Capital',
		'history_types_transfers'=>'Transfers',
		'history_types_rewards'=>'Rewards',
		'history_types_dao'=>'DAO',
		'history_types_subscriptions'=>'Subscriptions',
		'history_types_all'=>'All',
		'history_search'=>'Search',

		'history_date'=>'Datetime (GMT)',
		'history_op'=>'Operation',
		'history_descr'=>'Description',

		'history_load_more'=>'Show more records&hellip;',

		'count_all'=>'Total accounts:',
		'count_with_assets'=>'Of these have tokens viz:',
		'search_login'=>'Account name',
		'effective_capital_description'=>'Effective social capital including delegated outgoing and incoming capital',
		'effective_capital'=>'Effective capital',
		'self_capital'=>'Owned capital',
		'balance'=>'Balance',
		'balance_description'=>'Balance of transferable VIZ tokens',
		'summary'=>'Summary',
		'summary_description'=>'Summary amount of owned capital and tokens',

		'empty_result'=>'Nothing was found, try other search options.',
		'prev_page'=>'&larr; Previous page',
		'next_page'=>'Next page &rarr;',
	],
	'index'=>[
		'title'=>'Index',
		'witnesses_props'=>'Witnesses props',
		'witnesses_props_name'=>'Property',
		'witnesses_props_value'=>'Value',
		'state_title'=>'State of the VIZ blockchain',//State of the VIZ blockchain
		'state_date'=>'as of',//as of

		'activity'=>'Activity',

		'chart_amount'=>'Amount',
		'chart_accounts_amount'=>'Number of active accounts',
		'chart_in_30_days'=>'in 30 days',
		'chart_in_7_days'=>'in 7 days',
		'chart_in_1_day'=>'in 1 day',
		'chart_trx_amount'=>'Number of transactions',
		'chart_per_day'=>'per day',

		'period'=>'Period',
		'accounts'=>'Accounts',
		'in_30_days'=>'In 30 days',
		'in_7_days'=>'In 7 days',
		'in_1_day'=>'In 1 day',
		'in_1_hour'=>'In 1 hour',
		'blocks'=>'Blocks',
		'value'=>'Value',
		'average_block_size'=>'Average block size',
		'block_filling'=>'Block filling',
		'trx_count'=>'Transactions per day',
		'network_accessibility'=>'Network accessibility',
		'bandwidth_limitation'=>'Bandwidth limitation',

		'economy'=>'The Economy',//the
		'tokens'=>'Tokens',
		'amount'=>'Amount, viz',
		'liquid'=>'Liquid',
		'in_capital'=>'In capital',
		'dao_fund'=>'DAO Fund',
		'reward_fund'=>'Reward Fund',
		'total'=>'Total in the economy',
		'freezed'=>'Freezed',
		'summary'=>'Summary',

		'emission'=>'Emission',
		'witnesses'=>'Witnesses',
		'fixation_period'=>'Fixation period',
		'days'=>'days',
		'recalculation'=>'Distribution recalculation',//Distribution recalculation
		'total_per_year'=>'Total per year',

		'prop_title'=>'Property',
		'prop_value_range'=>'Value for the block range',
		'prop_witnesses_values'=>'Witnesses properties',
		'prop_table_witness'=>'Witness',
		'prop_table_name'=>'Property',
		'prop_change_dynamics'=>'Dynamics of change',
		'prop_table_time'=>'Datetime',
		'prop_table_value'=>'Value',

		'error'=>'Error',
		'page_not_found'=>'Page not found.',
	],
	'ops-history'=>[
		'award_to'=>'Rewarding <a class="view-account" href="/accounts/{receiver}/">{receiver}</a> for <span class="view-percent">{energy}%</span>',
		'award_from'=>'Received a reward from <a class="view-account" href="/accounts/{initiator}/">{initiator}</a> for <span class="view-percent">{energy}%</span>',
		'award_memo'=>' with memo ',
		'benefactor_award'=>'Received a beneficiaries reward <span class="view-tokens">{shares}</span> from <a class="view-account" href="/accounts/{initiator}/">{initiator}</a>',
		'benefactor_award_empty'=>'Received a empty beneficiary reward from <a class="view-account" href="/accounts/{initiator}/">{initiator}</a>',
		'receive_award'=>'<span class="view-tokens">{shares}</span> received as a reward from <a class="view-account" href="/accounts/{initiator}/">{initiator}</a>',
		'account_metadata'=>'Public profile updated',
		'account_create'=>'Created account <a class="view-account" href="/accounts/{account}/">{account}</a>',
		'account_create_tokens'=>' with transfer to social capital <span class="view-tokens">{tokens}</span>',
		'account_create_delegation'=>', delegated <span class="view-tokens">{shares}</span>',
		'create_invite'=>'A check for <span class="view-tokens">{tokens}</span> was created with a validation code <span class="view-key">{key}</span>',
		'claim_invite_balance'=>'A check with a code <span class="view-key">{key}</span> was redeemed',
		'use_invite_balance'=>'A check with a code <span class="view-key">{key}</span> was redeemed',
		'transfer_from'=>'<span class="view-tokens">{tokens}</span> transfered to <a class="view-account" href="/accounts/{to}/">{to}</a>',
		'transfer_to'=>'<span class="view-tokens">{tokens}</span> received from <a class="view-account" href="/accounts/{from}/">{from}</a>',
		'transfer_memo'=>' with memo ',
		'transfer_to_vesting_from'=>'<span class="view-tokens">{tokens}</span> stacked to social capital <a class="view-account" href="/accounts/{to}/">{to}</a>',
		'transfer_to_vesting_to'=>'<span class="view-tokens">{tokens}</span> received to social capital from <a class="view-account" href="/accounts/{from}/">{from}</a>',
		'withdraw_vesting'=>'A social capital withdraw has been initiated by the <span class="view-tokens">{shares}</span>',
		'withdraw_vesting_stop'=>'The withdrawal of social capital was stopped',
		'fill_vesting_withdraw'=>'<span class="view-tokens">{tokens}</span> received from withdrawal from social capital',
		'fill_vesting_withdraw_from'=>'To account <a class="view-account" href="/accounts/{to}/">{to}</a> have been transfered <span class="view-tokens">{tokens}</span> from the withdrawal of social capital',
		'fill_vesting_withdraw_to'=>'<span class="view-tokens">{tokens}</span> received from the withdrawal from social capital by the account <a class="view-account" href="/accounts/{from}/">{from}<a>',
		'delegate_vesting_shares_from'=>'<span class="view-tokens">{shares}</span> delegation received from <a class="view-account" href="/accounts/{from}/">{from}</a>',
		'delegate_vesting_shares_to'=>'<span class="view-tokens">{shares}</span> was delegated to <a class="view-account" href="/accounts/{to}/">{to}</a>',
		'delegate_vesting_shares_from_stop'=>'Delegation revoked from <a class="view-account" href="/accounts/{from}/">{from}</a>',
		'delegate_vesting_shares_to_stop'=>'Delegation revoked for <a class="view-account" href="/accounts/{to}/">{to}</a>',
		'return_vesting_delegation'=>'<span class="view-tokens">{shares}</span> returned to social capital from delegation',


		'committee_worker_create_request'=>'Creating a request to the DAO Fund, worker <a class="view-account" href="/accounts/{worker}/">{worker}</a>, requested amount <span class="view-tokens">{tokens}</span>, duration <span class="view-date">{days} days</span>, <span class="view-memo" data-text="{text}">information</span>',
		'committee_worker_cancel_request'=>'Cancellation of request №{request_id} in DAO Fund',
		'committee_vote_request'=>'Vote to <span class="view-percent">{percent}%</span> for request №{request_id} in DAO Fund',
		'committee_pay_request'=>'Received <span class="view-tokens">{tokens}</span> from DAO Fund by request №{request_id}',

		'account_update'=>'Updating access rights',

		'set_subaccount_price'=>'Putting subaccounts on sale for <span class="view-tokens">{tokens}</span>, beneficiary <a class="view-account" href="/accounts/{seller}/">{seller}</a>',
		'set_subaccount_price_stop'=>'Stop selling subaccounts',
		'set_account_price'=>'Account <a class="view-account" href="/accounts/{account}/">{account}</a> was on sale for <span class="view-tokens">{tokens}</span>, beneficiary <a class="view-account" href="/accounts/{seller}/">{seller}</a>',
		'set_account_price_stop'=>'Removing account from sale',
		'account_sale'=>'<a class="view-account" href="/accounts/{buyer}/">{buyer}</a> purchase <a class="view-account" href="/accounts/{account}/">{account}</a> from <a class="view-account" href="/accounts/{seller}/">{seller}</a> for <span class="view-tokens">{tokens}</span> ',
		'buy_account'=>'Purchasing an account <a class="view-account" href="/accounts/{account}/">{account}</a> fpr <span class="view-tokens">{tokens}</span>, transferred to capital <span class="view-tokens">{tokens_to_shares}</span>',

		'shutdown_witness'=>'Witness deactivated',
		'witness_update'=>'Witness was enabled, signing key <span class="view-key">{key}</span>, <span class="view-memo" data-text="{text}">information</span>',
		'witness_update_stop'=>'Witness was disabled',

		'account_witness_vote'=>'Vote for witness <a class="view-account" href="/witnesses/{witness}/">{witness}</a>',
		'account_witness_vote_stop'=>'Witness vote removed <a class="view-account" href="/witnesses/{witness}/">{witness}</a>',

		'account_witness_vote_income'=>'<a class="view-account" href="/accounts/{account}/">{account}</a> voted for witness',
		'account_witness_vote_income_stop'=>'<a class="view-account" href="/accounts/{account}/">{account}</a> removed his vote for witness',
		'paid_subscription_action_to'=>'Paying for a subscription to <a class="view-account" href="/accounts/{account}/">{account}</a>, level {level}, cost <span class="view-tokens">{summary_amount}</span>',
		'paid_subscription_action_from'=>'Subscription payment from <a class="view-account" href="/accounts/{subscriber}/">{subscriber}</a>, level {level}, cost <span class="view-tokens">{summary_amount}</span>',


		'cancel_paid_subscription_to'=>'End of subscription to <a class="view-account" href="/accounts/{account}/">{account}</a>',
		'cancel_paid_subscription_from'=>'End of subscription from <a class="view-account" href="/accounts/{subscriber}/">{subscriber}</a>',

		'paid_subscribe_to'=>'Subscriptions to the <a class="view-account" href="/accounts/{account}/">{account}</a>, level {level}, summary cost <span class="view-tokens">{summary_amount}</span>',
		'paid_subscribe_from'=>'Subscription from <a class="view-account" href="/accounts/{subscriber}/">{subscriber}</a>, level {level}, summary cost <span class="view-tokens">{summary_amount}</span>',
		'paid_subscribe_auto_renewal'=>' (auto-renewal enabled)',

		'set_paid_subscription'=>'Creating a paid subscription, <a href="{link}">terms and conditions</a>, maximum level: {levels}, cost per level: {amount}, subscription period (number of days): {period}',
		'invite_registration'=>'Registration by invite code <span class="view-key">{key}</span>',
	],
];