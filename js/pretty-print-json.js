//! pretty-print-json v0.2.1 ~ github.com/center-key/pretty-print-json ~ MIT License
const prettyPrintJson = {
   version: '0.2.1',
   toHtml(thing, options) {
      const op_type_witnesses_keys={
      	'witness_reward':['witness'],
      	'account_witness_vote':['witness'],
      };
      const op_type_accounts_keys={
      	'award':['initiator','receiver'],
      	'benefactor_award':['initiator','benefactor'],
      	'receive_award':['initiator','receiver'],
      	'account_metadata':['account'],
      	'transfer':['from','to'],
      	'transfer_to_vesting':['from','to'],
      	'withdraw_vesting':['account'],
      	'delegate_vesting_shares':['delegator','delegatee'],
      	'create_invite':['creator'],
      	'claim_invite_balance':['initiator','receiver'],
      	'committee_worker_create_request':['creator','worker'],
      	'committee_vote_request':['voter'],
      	'account_witness_vote':['account'],
      	'account_create':['creator','new_account_name'],
      	'set_account_price':['account','account_seller'],
      	'set_subaccount_price':['account','subaccount_seller'],
      	'buy_account':['buyer','account'],
      	'account_sale':['account'],
      	'paid_subscribe':['subscriber','account'],
      	'paid_subscription_action':['subscriber','account'],
      };
      const defaults = { indent: 4, quoteKeys: false, escapeHtml: false, type:false };
      const settings = Object.assign(defaults, options);
      const htmlEntities = (string) => {
         // Makes text displayable in browsers
         return string
            .replace(/&/g,   '&amp;')
            .replace(/\\"/g, '&bsol;&quot;')
            .replace(/</g,   '&lt;')
            .replace(/>/g,   '&gt;');
         };
      const buildValueHtml = (value,key,op_type) => {
         // Returns a string like: "<span class=json-number>3.1415</span>"
         const strType =  /^"/.test(value) && 'string';
         const boolType = ['true', 'false'].includes(value) && 'boolean';
         const nullType = value === 'null' && 'null';
         const type =     boolType || nullType || strType || 'number';
			if(-1!=Object.keys(op_type_witnesses_keys).indexOf(op_type)){
				if(-1!=op_type_witnesses_keys[op_type].indexOf(key)){
					let buf=value;
					buf=buf.replace(/^\"+/g,'');
					buf=buf.replace(/\"+$/g,'');
					value='"<a class="view-account" href="/witnesses/'+buf+'/">'+buf+'</a>"';
				}
			}
			if(-1!=Object.keys(op_type_accounts_keys).indexOf(op_type)){
				if(-1!=op_type_accounts_keys[op_type].indexOf(key)){
					let buf=value;
					buf=buf.replace(/^\"+/g,'');
					buf=buf.replace(/\"+$/g,'');
					value='"<a class="view-account" href="/accounts/'+buf+'/">'+buf+'</a>"';
				}
			}
         return '<span class=json-' + type + '>' + value + '</span>';
         };
      const replacer = (match, p1, p2, p3, p4) => {
         // Converts the four parenthesized capture groups (indent, key, value, end) into HTML
         const part =       { indent: p1, key: p2, value: p3, end: p4 };
         console.log(part);
         const findName =   settings.quoteKeys ? /(.*)(): / : /"([\w]+)": |(.*): /;
         const indentHtml = part.indent || '';
         const keyName =    part.key && part.key.replace(findName, '$1$2');
         const keyHtml =    part.key ? '<span class=json-key>' + keyName + '</span>: ' : '';
         const valueHtml =  part.value ? buildValueHtml(part.value,keyName,settings.type) : '';
         const endHtml =    part.end || '';
         return indentHtml + keyHtml + valueHtml + endHtml;
         };
      const jsonLine = /^( *)("[^"]+": )?("[^"]*"|[\w.+-]*)?([{}[\],]*)?$/mg;
         // Regex parses each line of the JSON string into four parts:
         //    Capture group       Part        Description                  '   "active": true,'
         //    ------------------  ----------  ---------------------------  --------------------
         //    ( *)                p1: indent  Spaces for indentation       '   '
         //    ("[^"]+": )         p2: key     Key name                     '"active": '
         //    ("[^"]*"|[\w.+-]*)  p3: value   Key value                    'true'
         //    ([{}[\],]*)         p4: end     Line termination characters  ','
      var json = JSON.stringify(thing, null, settings.indent);
      if(settings.escapeHtml){
          return htmlEntities(json).replace(jsonLine, replacer);
      }
      else{
      	  json=json.split('\\"').join('%%_REPLACER_%%');
      	  json=json.replace(jsonLine, replacer);
      	  json=json.split('%%_REPLACER_%%').join('\\"');
          return json;
      }
      }

   };

if (typeof module === 'object')
   module.exports = prettyPrintJson;  //node module loading system (CommonJS)
if (typeof window === 'object')
   window.prettyPrintJson = prettyPrintJson;  //support both global and window property
