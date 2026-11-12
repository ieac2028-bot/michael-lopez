<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ChatbotTranslationService
{
    public const LANGUAGES = ['en', 'es', 'fil', 'zh-CN', 'ko', 'ja'];

    private const OFFLINE_TRANSLATIONS = [
        'bata' => ['translation' => 'child', 'language' => 'tl'],
        'ito na ako' => ['translation' => 'Here I am', 'language' => 'tl'],
        'kumusta' => ['translation' => 'How are you?', 'language' => 'tl'],
        'kamusta' => ['translation' => 'How are you?', 'language' => 'tl'],
        'salamat' => ['translation' => 'Thank you', 'language' => 'tl'],
        'maraming salamat' => ['translation' => 'Thank you very much', 'language' => 'tl'],
        'hola' => ['translation' => 'Hello', 'language' => 'es'],
        'gracias' => ['translation' => 'Thank you', 'language' => 'es'],
        '你好' => ['translation' => 'Hello', 'language' => 'zh'],
        '谢谢' => ['translation' => 'Thank you', 'language' => 'zh'],
        '안녕하세요' => ['translation' => 'Hello', 'language' => 'ko'],
        '감사합니다' => ['translation' => 'Thank you', 'language' => 'ko'],
        'こんにちは' => ['translation' => 'Hello', 'language' => 'ja'],
        'ありがとう' => ['translation' => 'Thank you', 'language' => 'ja'],
    ];

    /** @return array{translation: string, detectedLanguage: string|null} */
    public function translateToEnglish(string $text): array
    {
        $offline = self::OFFLINE_TRANSLATIONS[mb_strtolower(trim($text))] ?? null;
        if ($offline !== null) {
            return [
                'translation' => $offline['translation'],
                'detectedLanguage' => $offline['language'],
            ];
        }
        $endpoint = (string) config('services.libretranslate.url');
        if ($endpoint === '') throw new \RuntimeException('Translation service is not configured.');

        $payload = ['q' => $text, 'source' => 'auto', 'target' => 'en', 'format' => 'text'];
        $apiKey = (string) config('services.libretranslate.api_key');
        if ($apiKey !== '') $payload['api_key'] = $apiKey;

        try {
            $response = Http::acceptJson()->asJson()->timeout(12)->post($endpoint, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException) {
            throw new \RuntimeException('The translation service could not be reached. Check the internet connection or translation URL.');
        }

        if ($response->status() === 429) {
            throw new \RuntimeException('The translation service is busy or rate-limited. Please try again shortly.');
        }
        if (in_array($response->status(), [401, 403], true)) {
            throw new \RuntimeException('The translation service rejected the request. Check its URL and API key settings.');
        }
        if (! $response->successful()) {
            throw new \RuntimeException('The translation service returned an error. Check its status and try again.');
        }

        $translation = $response->json('translatedText');
        if (! is_string($translation) || trim($translation) === '') {
            throw new \RuntimeException('The translation service returned no translation.');
        }

        $detectedLanguage = $response->json('detectedLanguage.language');

        return [
            'translation' => trim($translation),
            'detectedLanguage' => is_string($detectedLanguage) ? $detectedLanguage : null,
        ];
    }
    private const LABELS = [
        'es' => [
            'English' => 'Inglés', 'Filipino' => 'Filipino', 'Spanish' => 'Español', 'Mandarin Chinese' => 'Chino mandarín', 'Korean' => 'Coreano', 'Japanese' => 'Japonés',
            'Send' => 'Enviar', 'Ask something…' => 'Escribe tu pregunta…', 'Choose chat language' => 'Elige el idioma del chat',
            'Help me find a practice area' => 'Ayúdame a encontrar un área legal', 'What practice areas are available?' => '¿Qué áreas legales hay?',
            'How do I request a consultation?' => '¿Cómo solicito una consulta?', 'Where is the office?' => '¿Dónde está la oficina?',
            'What are the office hours?' => '¿Cuál es el horario de atención?', 'How do I contact the firm?' => '¿Cómo contacto al bufete?',
            'How do I track my case?' => '¿Cómo consulto el estado de mi caso?', 'How is my information handled?' => '¿Cómo se maneja mi información?',
            'Work or employment' => 'Trabajo o empleo', 'Family or inheritance' => 'Familia o herencia', 'Property or land' => 'Propiedad o terreno',
            'Business or contracts' => 'Negocios o contratos', 'Tax concern' => 'Consulta tributaria', 'Immigration concern' => 'Consulta de inmigración',
            'Intellectual property' => 'Propiedad intelectual', 'Something else' => 'Otro tema', 'Browse practice areas' => 'Ver áreas legales',
            'How do I contact the firm?' => '¿Cómo contacto al bufete?', 'How do I find free legal aid?' => '¿Cómo encuentro asistencia legal gratuita?',
        ],
        'fil' => [
            'English' => 'Ingles', 'Filipino' => 'Filipino', 'Spanish' => 'Espanyol', 'Mandarin Chinese' => 'Mandarin Chinese', 'Korean' => 'Koreano', 'Japanese' => 'Hapones',
            'Send' => 'Ipadala', 'Ask something…' => 'I-type ang iyong tanong…', 'Choose chat language' => 'Piliin ang wika ng chat',
            'Help me find a practice area' => 'Tulungan akong humanap ng larangan ng batas', 'What practice areas are available?' => 'Anong mga larangan ng batas ang mayroon?',
            'How do I request a consultation?' => 'Paano ako hihiling ng konsultasyon?', 'Where is the office?' => 'Nasaan ang opisina?',
            'What are the office hours?' => 'Ano ang oras ng opisina?', 'How do I contact the firm?' => 'Paano ko makokontak ang law firm?',
            'How do I track my case?' => 'Paano ko masusubaybayan ang kaso ko?', 'How is my information handled?' => 'Paano pinangangasiwaan ang impormasyon ko?',
            'Work or employment' => 'Trabaho o empleo', 'Family or inheritance' => 'Pamilya o mana', 'Property or land' => 'Ari-arian o lupa',
            'Business or contracts' => 'Negosyo o kontrata', 'Tax concern' => 'Usapin sa buwis', 'Immigration concern' => 'Usapin sa imigrasyon',
            'Intellectual property' => 'Intelektuwal na ari-arian', 'Something else' => 'Iba pa', 'Browse practice areas' => 'Tingnan ang mga larangan ng batas',
        ],
        'zh-CN' => [
            'English' => '英语', 'Filipino' => '菲律宾语', 'Spanish' => '西班牙语', 'Mandarin Chinese' => '中文（普通话）', 'Korean' => '韩语', 'Japanese' => '日语',
            'Send' => '发送', 'Ask something…' => '请输入问题…', 'Choose chat language' => '选择聊天语言',
            'Help me find a practice area' => '帮我查找法律业务领域', 'What practice areas are available?' => '有哪些法律业务领域？',
            'How do I request a consultation?' => '如何申请咨询？', 'Where is the office?' => '办公室在哪里？',
            'What are the office hours?' => '办公时间是什么？', 'How do I contact the firm?' => '如何联系律师事务所？',
            'How do I track my case?' => '如何查询案件进度？', 'How is my information handled?' => '我的信息会如何处理？',
            'Work or employment' => '工作或劳动', 'Family or inheritance' => '家庭或继承', 'Property or land' => '房产或土地',
            'Business or contracts' => '商业或合同', 'Tax concern' => '税务问题', 'Immigration concern' => '移民问题',
            'Intellectual property' => '知识产权', 'Something else' => '其他', 'Browse practice areas' => '浏览法律业务领域',
        ],
        'ko' => [
            'English' => '영어', 'Filipino' => '필리핀어', 'Spanish' => '스페인어', 'Mandarin Chinese' => '중국어(보통화)', 'Korean' => '한국어', 'Japanese' => '일본어',
            'Send' => '보내기', 'Ask something…' => '질문을 입력하세요…', 'Choose chat language' => '채팅 언어 선택',
            'Help me find a practice area' => '적합한 법률 분야를 찾아주세요', 'What practice areas are available?' => '어떤 법률 분야가 있나요?',
            'How do I request a consultation?' => '상담은 어떻게 신청하나요?', 'Where is the office?' => '사무실은 어디에 있나요?',
            'What are the office hours?' => '업무 시간이 어떻게 되나요?', 'How do I contact the firm?' => '법률 사무소에 어떻게 연락하나요?',
            'How do I track my case?' => '사건 진행 상황은 어떻게 확인하나요?', 'How is my information handled?' => '개인정보는 어떻게 처리되나요?',
            'Work or employment' => '직장 또는 고용', 'Family or inheritance' => '가족 또는 상속', 'Property or land' => '부동산 또는 토지',
            'Business or contracts' => '사업 또는 계약', 'Tax concern' => '세금 문의', 'Immigration concern' => '이민 문의',
            'Intellectual property' => '지식재산권', 'Something else' => '기타', 'Browse practice areas' => '법률 분야 둘러보기',
        ],
        'ja' => [
            'English' => '英語', 'Filipino' => 'フィリピン語', 'Spanish' => 'スペイン語', 'Mandarin Chinese' => '中国語（標準語）', 'Korean' => '韓国語', 'Japanese' => '日本語',
            'Send' => '送信', 'Ask something…' => '質問を入力してください…', 'Choose chat language' => 'チャットの言語を選択',
            'Help me find a practice area' => '法律分野を探したい', 'What practice areas are available?' => 'どのような法律分野がありますか？',
            'How do I request a consultation?' => '相談を申し込むにはどうすればよいですか？', 'Where is the office?' => '事務所はどこですか？',
            'What are the office hours?' => '営業時間を教えてください', 'How do I contact the firm?' => '事務所への連絡方法を教えてください',
            'How do I track my case?' => '案件の進捗を確認するにはどうすればよいですか？', 'How is my information handled?' => '個人情報はどのように扱われますか？',
            'Work or employment' => '仕事・雇用', 'Family or inheritance' => '家族・相続', 'Property or land' => '不動産・土地',
            'Business or contracts' => '事業・契約', 'Tax concern' => '税務に関する相談', 'Immigration concern' => '入国管理に関する相談',
            'Intellectual property' => '知的財産', 'Something else' => 'その他', 'Browse practice areas' => '法律分野を見る',
        ],
    ];

    private const STRINGS = [
        'es' => [
            'greeting' => 'Hola, soy el asistente del sitio de GPMO. Puedo responder preguntas básicas sobre este sitio y sus servicios. No puedo ofrecer asesoría legal.',
            'scope' => 'Ofrezco información general sobre el bufete y este sitio. No puedo dar asesoría legal ni evaluar un caso individual.',
            'advice' => 'No puedo ofrecer asesoría legal, evaluar elegibilidad ni predecir el resultado de un asunto. Para un caso real, consulte directamente con un abogado calificado o un proveedor de asistencia legal.',
            'finder' => 'Puedo ayudarle a elegir una categoría inicial para explorar, pero no a determinar la clasificación legal de un caso real. Elija el tema general que más se aproxime:',
            'area' => 'Para explorar el sitio, el área de {area} podría ser un buen punto de partida. Es solo una sugerencia de navegación, no una evaluación legal. Un profesional calificado tendría que revisar su situación.',
            'office' => 'La página de Contacto indica que el horario es de lunes a viernes, de 9:00 a 17:00. Comuníquese directamente con la oficina para confirmar los días festivos.',
            'address' => 'La dirección indicada es SAFA Building, 5858 Alfonso cor. Fermina Streets, Poblacion, Makati City 1210, Metro Manila.',
            'contact' => 'Puede llamar al +63 2 8897 0527 al 30 o al +63 917 164 0184, o escribir a safa@safalaw.com.ph. La página de Contacto ofrece más detalles.',
            'privacy' => 'Este es un prototipo de proyecto y no está conectado a los sistemas activos de GPMO. No ingrese datos confidenciales, números de identidad ni documentos sensibles.',
            'fees' => 'Este proyecto no publica los honorarios ni las condiciones de consulta. Comuníquese directamente con la oficina para preguntar por las tarifas vigentes.',
            'practice' => 'Puede explorar las categorías y descripciones en la página de Áreas de práctica. La lista sirve para navegar y no determina qué ley se aplica a un caso real.',
            'examples' => 'Los perfiles y flujos de este sitio son ejemplos; no confirman personal, credenciales, disponibilidad, referencias ni citas reales.',
            'booking' => 'El formulario de consulta demuestra cómo el proyecto captura una solicitud. No reserva una cita real ni se conecta al sistema de agenda del bufete.',
            'fallback' => 'No tengo una traducción revisada para esta respuesta. Se muestra en inglés a continuación:',
            'languages' => 'Las traducciones son orientativas. Para información legal importante, confirme el significado con la oficina.',
        ],
        'fil' => [
            'greeting' => 'Kumusta, ako ang site assistant ng GPMO. Makakasagot ako sa mga simpleng tanong tungkol sa website. Hindi ako makapagbibigay ng legal na payo.',
            'scope' => 'Nagbibigay ako ng pangkalahatang impormasyon tungkol sa law firm at website. Hindi ako makapagbibigay ng legal na payo o susuri ng partikular na kaso.',
            'advice' => 'Hindi ako makapagbibigay ng legal na payo, susuri ng pagiging kwalipikado, o huhula sa magiging resulta ng kaso. Para sa totoong usapin, direktang kumonsulta sa kwalipikadong abogado o legal-aid provider.',
            'finder' => 'Matutulungan kitang pumili ng panimulang kategorya para mag-browse, pero hindi matutukoy ang legal na pag-uuri ng totoong kaso. Piliin ang pinakamalapit na paksa:',
            'area' => 'Para sa pag-browse, maaaring magandang panimula ang larangan ng {area}. Gabay lang ito sa pag-navigate, hindi legal na pagsusuri. Kailangang suriin ng kwalipikadong propesyonal ang aktuwal mong usapin.',
            'office' => 'Ayon sa Contact page, bukas ang opisina Lunes hanggang Biyernes, 9 AM–5 PM. Direktang kontakin ang opisina para kumpirmahin ang oras kapag holiday.',
            'address' => 'Nakalista ang opisina sa SAFA Building, 5858 Alfonso cor. Fermina Streets, Poblacion, Makati City 1210, Metro Manila.',
            'contact' => 'Tumawag sa +63 2 8897 0527 hanggang 30 o +63 917 164 0184, o mag-email sa safa@safalaw.com.ph. Tingnan ang Contact page para sa iba pang detalye.',
            'privacy' => 'Ang mga isinusumiteng detalye ay iniimbak sa database ng website. Huwag maglagay ng kumpidensyal na impormasyon, ID number, o sensitibong dokumento.',
            'fees' => 'Walang nakalistang bayarin o kondisyon ng konsultasyon sa proyektong ito. Direktang kontakin ang opisina para itanong ang kasalukuyang singil.',
            'practice' => 'Tingnan ang mga kategorya at paglalarawan sa Practice Areas page. Gabay lamang ito sa pag-browse at hindi pagtukoy kung anong batas ang naaangkop sa totoong kaso.',
            'examples' => 'Mga halimbawang profile at proseso ang ipinapakita rito. Hindi nito kinukumpirma ang kasalukuyang kawani, kredensyal, availability, referral, o appointment.',
            'booking' => 'Ipinapakita ng consultation form kung paano kumukuha ng request ang demo. Hindi ito nagbu-book ng totoong appointment at hindi konektado sa scheduling system ng law firm.',
            'fallback' => 'Wala pang nasuring salin para sa sagot na ito. Ipinapakita ito sa Ingles sa ibaba:',
            'languages' => 'Pangkalahatang gabay lamang ang mga salin. Para sa mahalagang legal na impormasyon, kumpirmahin ito sa opisina.',
        ],
        'zh-CN' => [
            'greeting' => '您好，我是GPMO网站助手。我可以回答有关本网站的基本问题，但不能提供法律意见。',
            'scope' => '我提供有关律师事务所和本网站的一般信息，不能提供法律意见或评估个案。',
            'advice' => '我不能提供法律意见、判断资格或预测案件结果。如有实际法律问题，请直接咨询合格律师或法律援助机构。',
            'finder' => '我可以帮助您选择浏览网站时的起始类别，但不能判断真实案件的法律分类。请选择最接近的主题：',
            'area' => '浏览网站时，{area}可能是一个起点。这只是网站导航建议，并非法律评估。实际情况需要由合格的法律专业人士审查。',
            'office' => '联系页面列出的办公时间为周一至周五上午9点至下午5点。节假日时间请直接向办公室确认。',
            'address' => '网站列出的地址：SAFA Building, 5858 Alfonso cor. Fermina Streets, Poblacion, Makati City 1210, Metro Manila。',
            'contact' => '电话：+63 2 8897 0527 至 30 或 +63 917 164 0184；电子邮件：safa@safalaw.com.ph。联系页面也提供其他信息。',
            'privacy' => '这是演示项目，未连接到GPMO的正式系统。请勿输入机密事实、身份证号码或敏感文件。',
            'fees' => '本项目没有列出律师费或咨询条件。请直接联系办公室询问当前费用。',
            'practice' => '您可以在“业务领域”页面浏览类别和说明。此列表仅用于浏览，不能确定真实案件适用的法律。',
            'examples' => '本网站显示的律师资料和流程仅供参考，不代表实际员工、资历、可预约时间、转介或预约确认。',
            'booking' => '咨询表单用于演示项目如何收集请求，不会预约真实会面，也未连接律师事务所的日程系统。',
            'fallback' => '此回答暂无经过审核的中文译文。以下显示英文原文：',
            'languages' => '译文仅供一般参考。重要法律信息请向办公室确认。',
        ],
        'ko' => [
            'greeting' => '안녕하세요. GPMO 사이트 도우미입니다. 웹사이트에 관한 기본 질문에 답할 수 있지만 법률 자문은 제공하지 않습니다.',
            'scope' => '법률 사무소와 웹사이트에 대한 일반 정보를 제공합니다. 법률 자문이나 개별 사건 평가는 할 수 없습니다.',
            'advice' => '법률 자문, 자격 여부 판단 또는 사건 결과 예측은 제공할 수 없습니다. 실제 문제는 자격을 갖춘 변호사나 법률 지원 기관에 직접 문의하세요.',
            'finder' => '사이트 탐색을 위한 시작 분야를 고르는 데 도움을 드릴 수 있지만 실제 사건의 법적 분류를 판단하지는 않습니다. 가장 가까운 주제를 선택하세요:',
            'area' => '사이트를 둘러볼 때 {area} 분야부터 살펴볼 수 있습니다. 이는 탐색을 위한 제안일 뿐 법률적 평가가 아닙니다. 실제 사안은 자격 있는 전문가가 검토해야 합니다.',
            'office' => 'Contact 페이지의 안내에 따르면 업무 시간은 월요일부터 금요일, 오전 9시부터 오후 5시까지입니다. 공휴일 운영 여부는 사무실에 확인하세요.',
            'address' => '사이트에 기재된 주소: SAFA Building, 5858 Alfonso cor. Fermina Streets, Poblacion, Makati City 1210, Metro Manila.',
            'contact' => '전화: +63 2 8897 0527~30 또는 +63 917 164 0184, 이메일: safa@safalaw.com.ph. Contact 페이지에서 추가 정보를 확인할 수 있습니다.',
            'privacy' => '이 사이트는 시범 프로젝트이며 GPMO의 실제 시스템과 연결되어 있지 않습니다. 기밀 사실, 신분증 번호 또는 민감한 문서를 입력하지 마세요.',
            'fees' => '이 프로젝트에는 수임료와 상담 조건이 안내되어 있지 않습니다. 현재 요금은 사무실에 직접 문의하세요.',
            'practice' => 'Practice Areas 페이지에서 분야별 설명을 볼 수 있습니다. 이 목록은 탐색용이며 실제 사건에 적용되는 법률을 판단하지 않습니다.',
            'examples' => '이 사이트의 프로필과 절차는 참고용 예시이며 실제 직원, 자격, 예약 가능 여부, 소개 또는 예약을 확인하지 않습니다.',
            'booking' => '상담 양식은 프로젝트의 요청 접수 예시입니다. 실제 예약을 만들거나 사무실 일정 시스템에 연결되지 않습니다.',
            'fallback' => '이 답변의 검토된 한국어 번역은 아직 없습니다. 아래에 영어 원문을 표시합니다:',
            'languages' => '번역은 일반적인 참고용입니다. 중요한 법률 정보는 사무실에 확인하세요.',
        ],
        'ja' => [
            'greeting' => 'こんにちは。GPMOのサイトアシスタントです。ウェブサイトについての基本的な質問にお答えします。法律相談はできません。',
            'scope' => '法律事務所とウェブサイトに関する一般情報を提供します。法律相談や個別案件の評価はできません。',
            'advice' => '法律相談、資格の判断、案件の結果予測はできません。実際の問題については、資格のある弁護士または法律支援機関に直接ご相談ください。',
            'finder' => 'サイトを閲覧するための最初のカテゴリ選びをお手伝いできますが、実際の案件の法的分類は判断できません。最も近いテーマを選んでください：',
            'area' => 'サイトを閲覧する際は、{area}が最初の参考になる場合があります。これは閲覧用の案内であり、法律的な評価ではありません。実際の問題は資格のある専門家による確認が必要です。',
            'office' => 'Contactページによると、営業時間は月曜日から金曜日の午前9時から午後5時です。祝日の営業は事務所に直接ご確認ください。',
            'address' => '掲載住所：SAFA Building, 5858 Alfonso cor. Fermina Streets, Poblacion, Makati City 1210, Metro Manila。',
            'contact' => '電話：+63 2 8897 0527～30 または +63 917 164 0184、メール：safa@safalaw.com.ph。Contactページにも詳細があります。',
            'privacy' => 'これはデモ用プロジェクトで、GPMOの実際のシステムには接続されていません。機密情報、身分証番号、重要書類を入力しないでください。',
            'fees' => 'このプロジェクトには報酬や相談条件の記載がありません。現在の料金は事務所に直接お問い合わせください。',
            'practice' => 'Practice Areasページで分野と説明をご覧いただけます。この一覧は閲覧用であり、実際の案件に適用される法律を判断するものではありません。',
            'examples' => 'このサイトのプロフィールや手続きは参考例であり、実際の職員、資格、対応状況、紹介、予約を確認するものではありません。',
            'booking' => '相談フォームはプロジェクトの申込例を示すものです。実際の予約は行われず、事務所の予約システムにも接続されていません。',
            'fallback' => 'この回答には確認済みの日本語訳がありません。以下に英語の原文を表示します：',
            'languages' => '翻訳は一般的な参考情報です。重要な法律情報は事務所にご確認ください。',
        ],
    ];

    private const TERMS = [
        'es' => ['empleo'=>'work or employment','trabajo'=>'work or employment','familia'=>'family or inheritance','herencia'=>'family or inheritance','propiedad'=>'property or land','terreno'=>'property or land','negocio'=>'business or contracts','contrato'=>'business or contracts','impuestos'=>'tax concern','imigración'=>'immigration concern','inmigración'=>'immigration concern','honorarios'=>'fee','costo'=>'fee','horario'=>'office hours','dirección'=>'address','contacto'=>'contact the firm','consulta'=>'request a consultation','privacidad'=>'privacy','documentos'=>'what documents','¿Qué áreas legales hay?'=>'what practice areas','Ayúdame a encontrar un área legal'=>'help me find a practice area','¿Cómo solicito una consulta?'=>'request a consultation','¿Dónde está la oficina?'=>'where is the office','¿Cuál es el horario de atención?'=>'office hours','¿Cómo contacto al bufete?'=>'contact the firm','¿Cómo consulto el estado de mi caso?'=>'track my case','¿Cómo se maneja mi información?'=>'privacy','¿Cómo funciona la selección?'=>'how does matching work','¿Cómo encuentro asistencia legal gratuita?'=>'free legal aid','Mostrar todas las áreas legales'=>'show all practice areas','¿Qué documentos podrían necesitarse?'=>'what documents do i need','Ver áreas legales'=>'what practice areas','Ver todas las áreas legales →'=>'show all practice areas'],
        'fil' => ['trabaho'=>'work or employment','sahod'=>'work or employment','pamilya'=>'family or inheritance','mana'=>'family or inheritance','lupa'=>'property or land','ari-arian'=>'property or land','negosyo'=>'business or contracts','kontrata'=>'business or contracts','buwis'=>'tax concern','imigrasyon'=>'immigration concern','bayad'=>'fee','oras'=>'office hours','address'=>'address','opisina'=>'address','kontak'=>'contact the firm','konsultasyon'=>'request a consultation','pribado'=>'privacy','dokumento'=>'what documents','larangan ng batas'=>'help me find a practice area','Anong mga larangan ng batas ang mayroon?'=>'what practice areas','Paano ako hihiling ng konsultasyon?'=>'request a consultation','Nasaan ang opisina?'=>'where is the office','Ano ang oras ng opisina?'=>'office hours','Paano ko makokontak ang law firm?'=>'contact the firm','Paano ko masusubaybayan ang kaso ko?'=>'track my case','Paano pinangangasiwaan ang impormasyon ko?'=>'privacy','Paano gumagana ang pagtutugma?'=>'how does matching work','Paano ako makakahanap ng libreng tulong legal?'=>'free legal aid','Ipakita ang lahat ng larangan ng batas'=>'show all practice areas','Anong mga dokumento ang maaaring kailanganin?'=>'what documents do i need','Tingnan ang mga larangan ng batas'=>'what practice areas'],
        'zh-CN' => ['工作'=>'work or employment','劳动'=>'work or employment','家庭'=>'family or inheritance','继承'=>'family or inheritance','房产'=>'property or land','土地'=>'property or land','商业'=>'business or contracts','合同'=>'business or contracts','税务'=>'tax concern','移民'=>'immigration concern','费用'=>'fee','办公时间'=>'office hours','地址'=>'address','联系'=>'contact the firm','咨询'=>'request a consultation','隐私'=>'privacy','文件'=>'what documents','帮我查找法律业务领域'=>'help me find a practice area','有哪些法律业务领域？'=>'what practice areas','如何申请咨询？'=>'request a consultation','办公室在哪里？'=>'where is the office','办公时间是什么？'=>'office hours','如何联系律师事务所？'=>'contact the firm','如何查询案件进度？'=>'track my case','我的信息会如何处理？'=>'privacy','律师匹配如何运作？'=>'how does matching work','可以找到免费法律援助吗？'=>'free legal aid','显示所有业务领域'=>'show all practice areas','可能需要哪些文件？'=>'what documents do i need','浏览法律业务领域'=>'what practice areas'],
        'ko' => ['직장'=>'work or employment','고용'=>'work or employment','가족'=>'family or inheritance','상속'=>'family or inheritance','부동산'=>'property or land','토지'=>'property or land','사업'=>'business or contracts','계약'=>'business or contracts','세금'=>'tax concern','이민'=>'immigration concern','비용'=>'fee','업무 시간'=>'office hours','주소'=>'address','연락'=>'contact the firm','상담'=>'request a consultation','개인정보'=>'privacy','서류'=>'what documents','적합한 법률 분야를 찾아주세요'=>'help me find a practice area','어떤 법률 분야가 있나요?'=>'what practice areas','상담은 어떻게 신청하나요?'=>'request a consultation','사무실은 어디에 있나요?'=>'where is the office','업무 시간이 어떻게 되나요?'=>'office hours','법률 사무소에 어떻게 연락하나요?'=>'contact the firm','사건 진행 상황은 어떻게 확인하나요?'=>'track my case','개인정보는 어떻게 처리되나요?'=>'privacy','변호사 연결은 어떻게 이루어지나요?'=>'how does matching work','무료 법률 지원을 받을 수 있나요?'=>'free legal aid','모든 법률 분야 보기'=>'show all practice areas','어떤 서류가 필요할 수 있나요?'=>'what documents do i need','법률 분야 둘러보기'=>'what practice areas'],
        'ja' => ['仕事'=>'work or employment','雇用'=>'work or employment','家族'=>'family or inheritance','相続'=>'family or inheritance','不動産'=>'property or land','土地'=>'property or land','事業'=>'business or contracts','契約'=>'business or contracts','税務'=>'tax concern','移民'=>'immigration concern','料金'=>'fee','営業時間'=>'office hours','住所'=>'address','連絡'=>'contact the firm','相談'=>'request a consultation','個人情報'=>'privacy','書類'=>'what documents','法律分野を探したい'=>'help me find a practice area','どのような法律分野がありますか？'=>'what practice areas','相談を申し込むにはどうすればよいですか？'=>'request a consultation','事務所はどこですか？'=>'where is the office','営業時間を教えてください'=>'office hours','事務所への連絡方法を教えてください'=>'contact the firm','案件の進捗を確認するにはどうすればよいですか？'=>'track my case','個人情報はどのように扱われますか？'=>'privacy','弁護士の紹介はどのように行われますか？'=>'how does matching work','無料の法律支援を利用できますか？'=>'free legal aid','すべての法律分野を見る'=>'show all practice areas','どのような書類が必要ですか？'=>'what documents do i need','法律分野を見る'=>'what practice areas'],
    ];

    public function normalizeInput(string $text, string $language): string
    {
        foreach (self::TERMS[$language] ?? [] as $term => $english) {
            if (mb_stripos($text, $term) !== false) {
                $text .= ' ' . $english;
            }
        }
        return mb_substr($text, 0, 500);
    }

    public function translateReply(string $text, string $language): string
    {
        if (! isset(self::STRINGS[$language])) return $text;
        $pack = self::STRINGS[$language];

        if (str_contains($text, "I can't give legal advice") || str_contains($text, 'cannot give legal advice')) return $pack['advice'];
        if (str_contains($text, 'choose a starting category')) return $pack['finder'];
        if (preg_match('/^For browsing, (.+?) may be a useful starting category/', $text, $m)) return str_replace('{area}', $this->translateLabel($m[1], $language), $pack['area']);
        if (str_contains($text, 'office hours as Monday')) return $pack['office'];
        if (str_contains($text, 'The office is listed at')) return $pack['address'];
        if (str_starts_with($text, 'You can call +63')) return $pack['contact'];
        if (str_starts_with($text, 'Fees and consultation terms')) return $pack['fees'];
        if (str_starts_with($text, 'Contact and consultation submissions')) return $pack['privacy'];
        if (str_contains($text, 'Practice Areas page') || str_contains($text, 'practice-area categories')) return $pack['practice'];
        if (str_contains($text, 'sample attorney') || str_contains($text, 'sample data') || str_contains($text, 'demonstration profiles')) return $pack['examples'];
        if (str_contains($text, 'consultation form') || str_contains($text, 'consultation page')) return $pack['booking'];
        return $pack['fallback'] . "\n\n" . $text;
    }

    public function translateLabel(string $text, string $language): string
    {
        $map = self::LABELS[$language] ?? [];
        $extra = [
            'es' => ['How do I find free legal aid?' => '¿Cómo encuentro asistencia legal gratuita?', 'View sample request form →' => 'Ver el formulario de solicitud de muestra →', 'View sample consultation form →' => 'Ver el formulario de consulta de muestra →', 'Explore sample practice areas →' => 'Explorar áreas legales de muestra →', 'Browse practice areas →' => 'Ver áreas legales →', 'How does matching work?' => '¿Cómo funciona la selección?', 'Can I find free legal aid?' => '¿Puedo encontrar asistencia legal gratuita?', 'Show all practice areas' => 'Mostrar todas las áreas legales', 'What documents might be needed?' => '¿Qué documentos podrían necesitarse?', 'View contact information →' => 'Ver información de contacto →', 'View demo request page →' => 'Ver el formulario de demostración →', 'Open Client Portal →' => 'Abrir el portal del cliente →', 'Browse all practice areas →' => 'Ver todas las áreas legales →', 'Explore ' => 'Explorar ', ' →' => ' →', 'Labor Law' => 'Derecho laboral', 'Family Relations and Estate Planning' => 'Derecho de familia y planificación patrimonial', 'Property' => 'Propiedad', 'Commercial Law' => 'Derecho comercial', 'Taxation' => 'Impuestos', 'Immigration Law' => 'Derecho migratorio', 'Intellectual Property' => 'Propiedad intelectual'],
            'fil' => ['How do I find free legal aid?' => 'Paano ako makakahanap ng libreng tulong legal?', 'View sample request form →' => 'Tingnan ang sample request form →', 'View sample consultation form →' => 'Tingnan ang sample consultation form →', 'Explore sample practice areas →' => 'Tingnan ang mga sample na larangan ng batas →', 'Browse practice areas →' => 'Tingnan ang mga larangan ng batas →', 'How does matching work?' => 'Paano gumagana ang pagtutugma?', 'Can I find free legal aid?' => 'May mahahanap ba akong libreng tulong legal?', 'Show all practice areas' => 'Ipakita ang lahat ng larangan ng batas', 'What documents might be needed?' => 'Anong mga dokumento ang maaaring kailanganin?', 'View contact information →' => 'Tingnan ang contact information →', 'View demo request page →' => 'Tingnan ang demo request form →', 'Open Client Portal →' => 'Buksan ang Client Portal →', 'Browse all practice areas →' => 'Tingnan lahat ng larangan ng batas →', 'Labor Law' => 'Batas sa paggawa', 'Family Relations and Estate Planning' => 'Ugnayang pampamilya at pagpaplano ng ari-arian', 'Property' => 'Ari-arian', 'Commercial Law' => 'Batas pangkalakalan', 'Taxation' => 'Buwis', 'Immigration Law' => 'Batas sa imigrasyon', 'Intellectual Property' => 'Intelektuwal na ari-arian'],
            'zh-CN' => ['How does matching work?' => '律师匹配如何运作？', 'Can I find free legal aid?' => '可以找到免费法律援助吗？', 'Show all practice areas' => '显示所有业务领域', 'What documents might be needed?' => '可能需要哪些文件？', 'View contact information →' => '查看联系信息 →', 'View demo request page →' => '查看演示申请表 →', 'Open Client Portal →' => '打开客户门户 →', 'Browse all practice areas →' => '浏览所有业务领域 →', 'Labor Law' => '劳动法', 'Family Relations and Estate Planning' => '家庭关系与遗产规划', 'Property' => '财产法', 'Commercial Law' => '商法', 'Taxation' => '税法', 'Immigration Law' => '移民法', 'Intellectual Property' => '知识产权'],
            'ko' => ['How does matching work?' => '변호사 연결은 어떻게 이루어지나요?', 'Can I find free legal aid?' => '무료 법률 지원을 받을 수 있나요?', 'Show all practice areas' => '모든 법률 분야 보기', 'What documents might be needed?' => '어떤 서류가 필요할 수 있나요?', 'View contact information →' => '연락처 보기 →', 'View demo request page →' => '요청 양식 예시 보기 →', 'Open Client Portal →' => '고객 포털 열기 →', 'Browse all practice areas →' => '모든 법률 분야 둘러보기 →', 'Labor Law' => '노동법', 'Family Relations and Estate Planning' => '가족 관계 및 상속 계획', 'Property' => '부동산법', 'Commercial Law' => '상법', 'Taxation' => '세법', 'Immigration Law' => '이민법', 'Intellectual Property' => '지식재산권'],
            'ja' => ['How does matching work?' => '弁護士の紹介はどのように行われますか？', 'Can I find free legal aid?' => '無料の法律支援を利用できますか？', 'Show all practice areas' => 'すべての法律分野を見る', 'What documents might be needed?' => 'どのような書類が必要ですか？', 'View contact information →' => '連絡先を見る →', 'View demo request page →' => '申込フォームの例を見る →', 'Open Client Portal →' => 'クライアントポータルを開く →', 'Browse all practice areas →' => 'すべての法律分野を見る →', 'Labor Law' => '労働法', 'Family Relations and Estate Planning' => '家族関係・相続計画', 'Property' => '不動産法', 'Commercial Law' => '商法', 'Taxation' => '税法', 'Immigration Law' => '移民法', 'Intellectual Property' => '知的財産'],
        ];
        if (isset($map[$text])) return $map[$text];
        if (isset($extra[$language][$text])) return $extra[$language][$text];
        foreach ($map as $english => $translated) {
            if (mb_strtolower($english) === mb_strtolower($text)) return $translated;
        }
        if (str_starts_with($text, 'Explore ')) {
            $area = str_replace(' →', '', substr($text, 8));
            $area = $extra[$language][$area] ?? $area;
            $prefix = ['es' => 'Explorar ', 'fil' => 'Tingnan ang ', 'zh-CN' => '浏览', 'ko' => '둘러보기: ', 'ja' => '分野を見る： '][$language] ?? 'Explore ';
            return $prefix . $area . ' →';
        }
        return $text;
    }

    public function interfaceText(string $key, string $language): string
    {
        return self::STRINGS[$language][$key] ?? self::STRINGS['es'][$key] ?? '';
    }

    public function quickReply(string $text, string $language): string
    {
        return $this->translateLabel($text, $language);
    }
}
