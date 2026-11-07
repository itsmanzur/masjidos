/** bn_BD + ar translations for the Custom Duas block/widget/docs strings. */
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const T = {
	'MasjidOS Custom Duas': [ 'MasjidOS কাস্টম দোয়া', 'أدعية MasjidOS المخصصة' ],
	'Show only the duas you added in MasjidOS > Duas Library.': [ 'MasjidOS > দোয়া লাইব্রেরিতে আপনার যোগ করা দোয়াগুলোই শুধু দেখান।', 'عرض الأدعية التي أضفتها فقط في MasjidOS > مكتبة الأدعية.' ],
	'Dua Library category': [ 'দোয়া লাইব্রেরি ক্যাটাগরি', 'تصنيف مكتبة الأدعية' ],
	'Categories come from MasjidOS > Duas Library.': [ 'ক্যাটাগরিগুলো MasjidOS > দোয়া লাইব্রেরি থেকে আসে।', 'التصنيفات تأتي من MasjidOS > مكتبة الأدعية.' ],
	'Add duas in MasjidOS > Duas Library and click Publish': [ 'MasjidOS > দোয়া লাইব্রেরিতে দোয়া যোগ করে প্রকাশ (Publish) করুন', 'أضف الأدعية في MasjidOS > مكتبة الأدعية ثم اضغط نشر' ],
	'Assign a Dua Category, then add category="slug" to show only that group': [ 'একটি দোয়া ক্যাটাগরি দিন, তারপর শুধু ওই গ্রুপ দেখাতে category="slug" যোগ করুন', 'عيّن تصنيفًا للدعاء ثم أضف category="slug" لعرض تلك المجموعة فقط' ],
	'Also available as the MasjidOS Custom Duas block and Elementor widget': [ 'MasjidOS কাস্টম দোয়া ব্লক ও Elementor উইজেট হিসেবেও পাওয়া যায়', 'متاح أيضًا ككتلة أدعية MasjidOS المخصصة وكأداة Elementor' ]
};
[ [ 'bn_BD-translations.json', 0 ], [ 'ar-translations.json', 1 ] ].forEach( ( [ f, i ] ) => {
	const p = path.join( __dirname, f );
	const raw = fs.readFileSync( p, 'utf8' );
	const d = JSON.parse( raw );
	for ( const k in T ) { d[ k ] = T[ k ][ i ]; }
	const eol = raw.includes( '\r\n' ) ? '\r\n' : '\n';
	fs.writeFileSync( p, JSON.stringify( d, null, '\t' ).replace( /\n/g, eol ) + eol );
	console.log( f, 'ok' );
} );
