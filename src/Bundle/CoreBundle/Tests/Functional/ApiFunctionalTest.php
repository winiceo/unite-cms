<?php
/**
 * Created by PhpStorm.
 * User: franzwilding
 * Date: 11.01.18
 * Time: 16:55
 */

namespace UniteCMS\CoreBundle\Tests\Functional;

use Symfony\Component\Form\Util\StringUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;
use UniteCMS\CoreBundle\Controller\GraphQLApiController;
use UniteCMS\CoreBundle\Entity\ApiKey;
use UniteCMS\CoreBundle\Entity\Content;
use UniteCMS\CoreBundle\Entity\Domain;
use UniteCMS\CoreBundle\Entity\DomainMember;
use UniteCMS\CoreBundle\Entity\Organization;
use UniteCMS\CoreBundle\Entity\View;
use UniteCMS\CoreBundle\Form\FieldableFormType;
use UniteCMS\CoreBundle\ParamConverter\IdentifierNormalizer;
use UniteCMS\CoreBundle\Service\UniteCMSManager;
use UniteCMS\CoreBundle\Tests\DatabaseAwareTestCase;

/**
 * @group slow
 */
class ApiFunctionalTestCase extends DatabaseAwareTestCase
{

    protected $data = [
        'foo_organization' => [
            '{
  "title": "Marketing & PR",
  "identifier": "marketing",
  "roles": [
    "ROLE_PUBLIC",
    "ROLE_EDITOR"
  ],
  "content_types": [
    {
      "title": "News",
      "identifier": "news",
      "fields": [
        {
          "title": "Title",
          "identifier": "title_title",
          "type": "text",
          "settings": {}
        },
        {
          "title": "Content",
          "identifier": "content",
          "type": "textarea",
          "settings": {}
        },
        {
          "title": "Category",
          "identifier": "category",
          "type": "reference",
          "settings": {
            "domain": "marketing",
            "content_type": "news_category"
          }
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    },
    {
      "title": "News Category",
      "identifier": "news_category",
      "fields": [
        {
          "title": "Name",
          "identifier": "name",
          "type": "text",
          "settings": {}
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    }
  ],
  "setting_types": [
    {
      "title": "Website",
      "identifier": "website",
      "fields": [
        {
          "title": "Title",
          "identifier": "title_title",
          "type": "text",
          "settings": {}
        },
        {
          "title": "Imprint",
          "identifier": "imprint",
          "type": "textarea",
          "settings": {}
        }
      ],
      "permissions": {
        "view setting": "true",
        "update setting": "true"
      },
      "locales": []
    }
  ]
}',
'{
  "title": "Internal Content",
  "identifier": "intern",
  "roles": [
    "ROLE_EDITOR"
  ],
  "content_types": [
    {
      "title": "Time Tracking",
      "identifier": "time_tracking",
      "fields": [
        {
          "title": "Employee",
          "identifier": "employee",
          "type": "text",
          "settings": {}
        },
        {
          "title": "Package",
          "identifier": "package",
          "type": "reference",
          "settings": {
            "domain": "intern",
            "content_type": "package"
          }
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    },
    {
      "title": "Working Packages",
      "identifier": "package",
      "fields": [
        {
          "title": "Title",
          "identifier": "title_title",
          "type": "text",
          "settings": {}
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    }
  ],
  "setting_types": []
}'
        ],
        'baa_organization' => [
            '{
  "title": "Marketing & PR",
  "identifier": "internal",
  "roles": [
    "ROLE_PUBLIC",
    "ROLE_EDITOR"
  ],
  "content_types": [
    {
      "title": "News",
      "identifier": "news",
      "fields": [
        {
          "title": "Title",
          "identifier": "title_title",
          "type": "text",
          "settings": {}
        },
        {
          "title": "Content",
          "identifier": "content",
          "type": "textarea",
          "settings": {}
        },
        {
          "title": "Category",
          "identifier": "category",
          "type": "reference",
          "settings": {
            "domain": "marketing",
            "content_type": "news_category"
          }
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    },
    {
      "title": "News Category",
      "identifier": "news_category",
      "fields": [
        {
          "title": "Name",
          "identifier": "name",
          "type": "text",
          "settings": {}
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    }
  ],
  "setting_types": [
    {
      "title": "Website",
      "identifier": "website",
      "fields": [
        {
          "title": "Title",
          "identifier": "title_title",
          "type": "text",
          "settings": {}
        },
        {
          "title": "Imprint",
          "identifier": "imprint",
          "type": "textarea",
          "settings": {}
        }
      ],
      "permissions": {
        "view setting": "true",
        "update setting": "true"
      },
      "locales": []
    }
  ]
}',
            '{
  "title": "Internal Content",
  "identifier": "intern",
  "content_types": [
    {
      "title": "Time Tracking",
      "identifier": "time_tracking",
      "fields": [
        {
          "title": "Employee",
          "identifier": "employee",
          "type": "text",
          "settings": {}
        },
        {
          "title": "Package",
          "identifier": "package",
          "type": "reference",
          "settings": {
            "domain": "intern",
            "content_type": "package"
          }
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    },
    {
      "title": "Working Packages",
      "identifier": "package",
      "fields": [
        {
          "title": "Title",
          "identifier": "title_title",
          "type": "text",
          "settings": {}
        }
      ],
      "views": [
        {
          "title": "All",
          "identifier": "all",
          "type": "table",
          "settings": {}
        }
      ],
      "locales": []
    }
  ],
  "setting_types": []
}'
        ],
    ];
    protected $member_types = ['editor', 'viewer'];

    /**
     * @var Domain[] $domains
     */
    protected $domains = [];

    /**
     * @var ApiKey[] $users
     */
    protected $users = [];

    /**
     * @var GraphQLApiController $controller
     */
    private $controller;

    public function setUp()
    {
        parent::setUp();

        // Create a full unite CMS structure with different organizations, domains and users.
        foreach($this->data as $id => $domains) {
            $org = new Organization();
            $org->setIdentifier($id)->setTitle(ucfirst($id));
            $this->em->persist($org);
            $this->em->flush($org);

            foreach($domains as $domain_data) {
                $domain = static::$container->get('unite.cms.domain_definition_parser')->parse($domain_data);
                $domain->setOrganization($org);
                $this->domains[$domain->getIdentifier()] = $domain;
                $this->em->persist($domain);
                $this->em->flush($domain);

                foreach($this->member_types as $mtype) {
                    $domainMember = new DomainMember();
                    $domainMember->setDomain($domain)->setDomainMemberType($domain->getDomainMemberTypes()->get($mtype));
                    $this->users[$domain->getIdentifier() . '_' . $mtype] = new ApiKey();
                    $this->users[$domain->getIdentifier() . '_' . $mtype]->setName($domain->getIdentifier() . '_' . $mtype)->setOrganization($org);
                    $this->users[$domain->getIdentifier() . '_' . $mtype]->addDomain($domainMember);

                    $this->em->persist($this->users[$domain->getIdentifier() . '_' . $mtype]);
                    $this->em->flush($this->users[$domain->getIdentifier() . '_' . $mtype]);
                }

                // For each content type create some views and test content.
                foreach($domain->getContentTypes() as $ct) {

                    $other = new View();
                    $other->setTitle('Other')->setIdentifier('other')->setType('table');
                    $ct->addView($other);
                    $this->em->persist($other);
                    $this->em->flush($other);

                    for($i = 0; $i < 60; $i++) {
                        $content = new Content();
                        $content->setContentType($ct);

                        $content_data = [];

                        foreach($ct->getFields() as $field) {
                            switch ($field->getType()) {
                                case 'text': $content_data[$field->getIdentifier()] = $this->generateRandomMachineName(100); break;
                                case 'textarea': $content_data[$field->getIdentifier()] = '<p>' . $this->generateRandomMachineName(100) . '</p>'; break;
                            }
                        }

                        $content->setData($content_data);
                        $this->em->persist($content);
                        $this->em->flush($content);
                    }

                    $this->em->refresh($ct);
                }

                $this->em->refresh($domain);
            }
        }

        $this->controller = new GraphQLApiController();
        $this->controller->setContainer(static::$container);
    }

    private function api(Domain $domain, UserInterface $user, string $query, array $variables = [], $set_csrf_token = FALSE, $firewall = 'api') {

        // Fake a real HTTP request.
        $request = new Request([], [], [
            'organization' => $domain->getOrganization(),
            'domain' => $domain,
        ], [], [], [
            'REQUEST_METHOD' => 'POST',
        ], json_encode(['query' => $query, 'variables' => $variables]));


        // For each request, initialize the cms manager.
        $requestStack = new RequestStack();
        $requestStack->push(new Request([], [], [
            'organization' => IdentifierNormalizer::denormalize($domain->getOrganization()->getIdentifier()),
            'domain' => $domain->getIdentifier(),
        ]));

        $reflector = new \ReflectionProperty(UniteCMSManager::class, 'requestStack');
        $reflector->setAccessible(true);
        $reflector->setValue(static::$container->get('unite.cms.manager'), $requestStack);

        $reflector = new \ReflectionMethod(UniteCMSManager::class, 'initialize');
        $reflector->setAccessible(true);
        $reflector->invoke(static::$container->get('unite.cms.manager'));

        // If we fallback to the statefull main firewall, we need to add a csrf-token with the request.
        if($set_csrf_token) {
            $request->headers->set('X-CSRF-TOKEN', static::$container->get('security.csrf.token_manager')->getToken(StringUtil::fqcnToBlockPrefix(FieldableFormType::class))->getValue());
        }

        static::$container->get('security.token_storage')->setToken(new PostAuthenticationGuardToken($user, $firewall, []));

        $response = $this->controller->indexAction($domain->getOrganization(), $domain, $request);
        return json_decode($response->getContent());
    }

    private function assertApiResponse($expected, $actual) {

        if(!is_string($expected)) {
            $expected = json_encode($expected);
        }

        $this->assertEquals(json_decode($expected), $actual);
    }

    public function testAccessingAPI() {
        $this->assertApiResponse([
            'data' => [
                'findNews' => [
                    'page' => 1
                ]
            ]
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query {
                findNews {
                    page
                }
            }')
        );

        $this->assertApiResponse([
            'data' => [
                'findNews' => [
                    'page' => 1
                ],
                'findNews_category' => [
                    'page' => 1
                ]
            ]
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query {
                findNews {
                    page
                },
                findNews_category {
                    page
                }
            }')
        );
    }

    public function testSpecialOperations() {
        $this->assertApiResponse([
            'data' => [
                'findNews' => [
                    'total' => 0
                ],
            ],
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query {
            findNews(filter: { field: "title_title", operator: "IS NULL" }) {
                total
            }
        }'));
        $this->assertApiResponse([
            'data' => [
                'findNews' => [
                    'total' => 60
                ],
            ],
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query {
            findNews(filter: { field: "title_title", operator: "IS NOT NULL" }) {
                total
            }
        }'));
    }

    public function testGenericApiFindMethod() {
        $result = $this->api(
        $this->domains['marketing'],
        $this->users['marketing_viewer'],'query {
          find(limit: 500, types: ["news", "news_category"]) {
            total,
            result {
              id,
              type
              
              ... on NewsContent {
                title_title
              }
              
              ... on News_categoryContent {
                name
              }
            }
          }
        }');

        // Result should contain 60x news and other 40x news_category
        $count_news = 0;
        $count_category = 0;

        foreach($result->data->find->result as $content) {
            if($content->type == 'news') {
                $count_news++;
            }
            if($content->type == 'news_category') {
                $count_category++;
            }
        }

        $this->assertEquals(60, $count_news);
        $this->assertEquals(40, $count_category);
    }

    public function testAPIFiltering() {

        // First get all news
        $news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query {
                findNews(limit: 100) {
                    total,
                    result {
                        id,
                        title_title,
                        content
                    }
                }
            }');

        // Get title_title and content partial strings from any random content.
        $content1 = $news->data->findNews->result[rand(1, $news->data->findNews->total - 1)];
        $content2 = $news->data->findNews->result[rand(1, $news->data->findNews->total - 1)];
        $content1_title_title_part = substr($content1->title_title, rand(1, 50), rand(1, 20));
        $content2_content_part = substr($content2->content, rand(1, 50), rand(1, 20));

        // Filter by exact title_title.
        $result = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query($value: String) {
                findNews(filter: { field: "title_title", operator: "=", value: $value }) {
                    total,
                    result {
                        id,
                        title_title,
                        content
                    }
                }
            }', [
                'value' => $content1->title_title
            ]
        );

        $this->assertGreaterThan(0, $result->data->findNews->total);
        $ids = [];
        foreach($result->data->findNews->result as $c) {
            $ids[] = $c->id;
        }
        $this->assertContains($content1->id, $ids);


        // Filter by exact title_title and exact content.
        $result = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query($title_title: String, $content: String) {
                findNews(filter: { AND: [
                    { field: "title_title", operator: "=", value: $title_title },
                    { field: "content", operator: "=", value: $content }
                ]}) {
                    total,
                    result {
                        id,
                        title_title,
                        content
                    }
                }
            }', [
                'title_title' => $content1->title_title,
                'content' => $content1->content,
            ]
        );

        $this->assertGreaterThan(0, $result->data->findNews->total);
        $ids = [];
        foreach($result->data->findNews->result as $c) {
            $ids[] = $c->id;
        }
        $this->assertContains($content1->id, $ids);


        // Filter by part title_title or part content.
        $result = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query($title_title: String, $content: String) {
                findNews(filter: { OR: [
                    { field: "title_title", operator: "LIKE", value: $title_title },
                    { field: "content", operator: "LIKE", value: $content }
                ]}) {
                    total,
                    result {
                        id,
                        title_title,
                        content
                    }
                }
            }', [
                'title_title' => '%' . $content1_title_title_part . '%',
                'content' => '%' . $content2_content_part . '%',
            ]
        );

        // This happens only sometimes, so for getting a chance to debug this we inject some debugging output here.
        if($result->data->findNews->total < 2) {
            print_r($content1_title_title_part);
            print_r($content1->title_title);
            print_r($content2_content_part);
            print_r($content2->content);
        }

        $this->assertGreaterThan(1, $result->data->findNews->total);
    }

    public function testAPISorting()
    {
        // Make two content have distinct created values
        $i = 1;
        $reflector = new \ReflectionProperty(Content::class, 'created');
        $reflector->setAccessible(true);
        foreach($this->domains['marketing']->getContentTypes()->first()->getContent() as $c) {
            $time = new \DateTime();
            $time->add(new \DateInterval('PT'.$i.'S'));
            $reflector->setValue($c, $time);

            if($i == 1) {
                $c->setData([
                    'title_title' => 'test_nested_sorting',
                    'content' => 'AAA',
                ]);
            }

            if($i == 2) {
                $c->setData([
                    'title_title' => 'test_nested_sorting',
                    'content' => 'ZZZ',
                ]);
            }

            $i++;
        }

        $this->em->flush();
        $this->em->refresh($this->domains['marketing']->getContentTypes()->first());
        $this->em->refresh($this->domains['marketing']);


        // First get all news
        $news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 2, sort: { field: "created", order: "ASC" }) {
                    total,
                    result {
                        created
                    }
                }
            }');

        $this->assertGreaterThan(0, $news->data->findNews->total);
        $this->assertTrue(($news->data->findNews->result[0]->created < $news->data->findNews->result[1]->created));

        $news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 2, sort: { field: "created", order: "DESC" }) {
                    total,
                    result {
                        created
                    }
                }
            }');

        $this->assertGreaterThan(0, $news->data->findNews->total);
        $this->assertTrue(($news->data->findNews->result[0]->created > $news->data->findNews->result[1]->created));

        $news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 2, 
                    filter: { field: "title_title", operator: "=", value: "test_nested_sorting" }, 
                    sort: { field: "content", order: "ASC" }) {
                    
                    total,
                    result {
                        content
                    }
                }
            }');

        $this->assertEquals(2, $news->data->findNews->total);
        $this->assertEquals('AAA', $news->data->findNews->result[0]->content);
        $this->assertEquals('ZZZ', $news->data->findNews->result[1]->content);

        $news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 2, 
                    filter: { field: "title_title", operator: "=", value: "test_nested_sorting" }, 
                    sort: { field: "content", order: "DESC" }) {
                    
                    total,
                    result {
                        content
                    }
                }
            }');

        $this->assertEquals(2, $news->data->findNews->total);
        $this->assertEquals('ZZZ', $news->data->findNews->result[0]->content);
        $this->assertEquals('AAA', $news->data->findNews->result[1]->content);

        $news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 2, 
                    filter: { field: "title_title", operator: "=", value: "test_nested_sorting" }, 
                    sort: [
                        { field: "title_title", order: "ASC" },
                        { field: "content", order: "ASC" }
                    ]) {
                    
                    total,
                    result {
                        content
                    }
                }
            }');

        $this->assertEquals(2, $news->data->findNews->total);
        $this->assertEquals('AAA', $news->data->findNews->result[0]->content);
        $this->assertEquals('ZZZ', $news->data->findNews->result[1]->content);

        $news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 2, 
                    filter: { field: "title_title", operator: "=", value: "test_nested_sorting" }, 
                    sort: [
                        { field: "title_title", order: "ASC" },
                        { field: "content", order: "DESC" }
                    ]) {
                    
                    total,
                    result {
                        content
                    }
                }
            }');

        $this->assertEquals(2, $news->data->findNews->total);
        $this->assertEquals('ZZZ', $news->data->findNews->result[0]->content);
        $this->assertEquals('AAA', $news->data->findNews->result[1]->content);
    }

    public function testAccessReferencedValue() {

        $category = $this->domains['marketing']->getContentTypes()->last()->getContent()->get(0);
        $news = $this->domains['marketing']->getContentTypes()->first()->getContent()->get(0);

        $news->setData([
            'title_title' => 'with_category',
            'category' => [
                'domain' => 'marketing',
                'content_type' => 'news_category',
                'content' => $category->getId(),
            ],
        ]);

        $this->em->flush();
        $this->em->refresh($this->domains['marketing']->getContentTypes()->first());
        $this->em->refresh($this->domains['marketing']);

        $this->assertApiResponse([
            'data' => [
                'findNews' => [
                    'total' => 1,
                    'result' => [
                        [
                            'id' => $news->getId(),
                            'category' => [
                                'id' => $category->getId(),
                                'name' => $category->getData()['name'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 1, filter: { field: "title_title", operator: "=", value: "with_category" }) {
                    total,
                    result {
                        id,
                        category {
                            id,
                            name
                        }
                    }
                }
            }'));
    }

    public function testGetContentAndSetting() {

        $setting = $this->domains['marketing']->getSettingTypes()->first()->getSetting();
        $setting->setData([
            'title_title' => $this->generateRandomMachineName(100),
            'imprint' => $this->generateRandomMachineName(100)
        ]);

        $this->em->persist($setting);
        $this->em->flush();
        $this->em->refresh($this->domains['marketing']->getSettingTypes()->first());
        $this->em->refresh($this->domains['marketing']);
        $content = $this->domains['marketing']->getContentTypes()->first()->getContent()->get(0);

        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query($newsID: ID!) {
                getNews(id: $newsID) {
                    id,
                    title_title,
                    content
                },
                WebsiteSetting {
                    title_title,
                    imprint
                }
            }', [
            'newsID' => $content->getId(),
        ]);

        $this->assertApiResponse([
            'data' => [
                'getNews' => [
                    'id' => $content->getId(),
                    'title_title' => $content->getData()['title_title'],
                    'content' => $content->getData()['content']
                ],
                'WebsiteSetting' => [
                    'title_title' => $setting->getData()['title_title'],
                    'imprint' => $setting->getData()['imprint']
                ],
            ],
        ], $response);
    }

    public function testContentPagination() {

        // Get all News ids.
        $ids = [];
        $all_news = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 100) {
                    total,
                    result {
                        id
                    }
                }
            }');

        foreach($all_news->data->findNews->result as $content) {
            $ids[] = $content->id;
        }

        // Test pagination with limit 0 and page 0.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(limit: 0, page: 1) { total, result { id } }
            }');
        $this->assertNull($response->data->findNews);
        $this->assertGreaterThan(0, count($response->errors));

        // Test pagination with too big offset.
        $this->assertApiResponse([
            'data' => [ 'findNews' => [ 'total' => 60, 'result' => [] ] ]
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'query {
                findNews(page: 1000) { total, result { id } }
            }'));

        // Test pagination with negative page should be the same as with page 1.
        $this->assertEquals(

            $this->api(
                $this->domains['marketing'],
                $this->users['marketing_viewer'], 'query {
                    findNews(page: 1) { total, result { id } }
                }'),
            $this->api(
                $this->domains['marketing'],
                $this->users['marketing_viewer'], 'query {
                    findNews(page: -5) { total, result { id } }
                }')
        );



        // Test pagination with random limit of 1 .. 1/4 of total.
        $page_size = rand(10, 15);
        $page = 1;
        while ($page * $page_size < $all_news->data->findNews->total) {
            $page_ids = [];
            $response = $this->api(
                $this->domains['marketing'],
                $this->users['marketing_viewer'], 'query($page: Int, $limit: Int) {
                    findNews(page: $page, limit: $limit) { total, result { id } }
                }', ['page' => $page, 'limit' => $page_size]);

            foreach($response->data->findNews->result as $content) {
                $page_ids[] = $content->id;
            }

            $this->assertEquals(array_slice($ids, ($page - 1) * $page_size, $page_size), $page_ids);
            $page++;
        }

    }

    public function testAPICreateAndUpdateMethod() {

        // Try to create content without permissions.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'mutation {
                createNews_category(data: { name: "First Category" }, persist: true) {
                    id, 
                    name
                }
            }');

        $this->assertNotEmpty($response->errors);
        $this->assertEquals("You are not allowed to create content in content type 'News Category'.", $response->errors[0]->message);

        // Try to create content with permissions but do not persist.
        $initialCount = $this->em->getRepository('UniteCMSCoreBundle:Content')->count([]);
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation {
                createNews_category(data: { name: "First Category" }, persist: false) {
                    id, 
                    name
                }
            }');

        $this->assertEquals($initialCount, $this->em->getRepository('UniteCMSCoreBundle:Content')->count([]));
        $this->assertTrue(empty($response->errors));
        $category = $response->data->createNews_category;
        $this->assertEmpty($category->id);
        $this->assertEquals('First Category', $category->name);

        // Try to create content with permissions.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation {
                createNews_category(data: { name: "First Category" }, persist: true) {
                    id, 
                    name
                }
            }');

        $this->assertEquals($initialCount+1, $this->em->getRepository('UniteCMSCoreBundle:Content')->count([]));
        $this->assertTrue(empty($response->errors));

        $category = $response->data->createNews_category;
        $this->assertEquals('First Category', $category->name);
        $this->assertNotEmpty($category->id);

        // Now create a news content with invalid content.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($category: ReferenceFieldTypeInput) {
                createNews(data: { title_title: "First News", content: "<p>Hello World</p>", category: $category }, persist: true) {
                    id, 
                    title_title,
                    content,
                    category {
                      id,
                      name
                    }
                }
            }', [
                'category' => [
                    'domain' => 'marketing',
                    'content_type' => 'news_category',
                    'content' => 'foo',
                ]
            ]);

        $this->assertNotEmpty($response->errors);
        $this->assertContains("ERROR: ".static::$container->get('translator')->trans('invalid_reference_definition', [], 'validators'), $response->errors[0]->message);

        // Now create a news content with valid content.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($category: ReferenceFieldTypeInput) {
                createNews(data: { title_title: "First News", content: "<p>Hello World</p>", category: $category }, persist: true) {
                    id, 
                    title_title,
                    content,
                    category {
                      id,
                      name
                    }
                }
            }', [
            'category' => [
                'domain' => 'marketing',
                'content_type' => 'news_category',
                'content' => $category->id,
            ]
        ]);

        $news = $response->data->createNews;
        $this->assertTrue(empty($response->errors));
        $this->assertNotEmpty($news->id);
        $this->assertNotEmpty($news->title_title);
        $this->assertNotEmpty($news->content);
        $this->assertNotEmpty($news->category);
        $this->assertEquals($category, $news->category);

        // Update the category, but with wrong user.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'], 'mutation($id: ID!) {
                updateNews_category(id: $id, data: { name: "Updated Category Title" }, persist: true) {
                    id, 
                    name
                }
            }', ['id' => $category->id]);

        $this->assertNotEmpty($response->errors);
        $this->assertEquals("You are not allowed to update content with id '" . $category->id . "'.", $response->errors[0]->message);

        // Update the category with right user.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($id: ID!) {
                updateNews_category(id: $id, data: { name: "Updated Category Title" }, persist: true) {
                    id, 
                    name
                }
            }', ['id' => $category->id]);

        $this->assertTrue(empty($response->errors));
        $this->assertEquals((object)[
            'id' => $category->id,
            'name' => 'Updated Category Title',
        ], $response->data->updateNews_category);

        // Update a news content with invalid content.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($id: ID!, $category: ReferenceFieldTypeInput) {
                updateNews(id: $id, data: { title_title: "Updated News", content: "<p>Hello new World</p>", category: $category }, persist: true) {
                    id, 
                    title_title,
                    content,
                    category {
                      id,
                      name
                    }
                }
            }', [
            'id' => $news->id,
            'category' => [
                'domain' => 'marketing',
                'content_type' => 'news_category',
                'content' => 'foo',
            ]
        ]);

        $this->assertNotEmpty($response->errors);
        $this->assertContains("ERROR: ".static::$container->get('translator')->trans('invalid_reference_definition', [], 'validators'), $response->errors[0]->message);

        // update a news content with valid content.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($id: ID!, $category: ReferenceFieldTypeInput) {
                updateNews(id: $id, data: { title_title: "Updated News", content: "<p>Hello new World</p>", category: $category }, persist: true) {
                    id, 
                    title_title,
                    content,
                    category {
                      id,
                      name
                    }
                }
            }', [
            'id' => $news->id,
            'category' => null
        ]);

        $this->assertTrue(empty($response->errors));
        $this->assertEquals($news->id, $response->data->updateNews->id);
        $this->assertEquals("Updated News", $response->data->updateNews->title_title);
        $this->assertEquals("<p>Hello new World</p>", $response->data->updateNews->content);
        $this->assertNull($response->data->updateNews->category);

        // update partial news content with valid content.
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($id: ID!) {
                updateNews(id: $id, data: { title_title: "Updated News2" }, persist: true) {
                    id, 
                    title_title,
                    content,
                    category {
                      id,
                      name
                    }
                }
            }', [
            'id' => $news->id,
            'category' => null
        ]);

        $this->assertTrue(empty($response->errors));
        $this->assertEquals($news->id, $response->data->updateNews->id);
        $this->assertEquals("Updated News2", $response->data->updateNews->title_title);
        $this->assertEquals("<p>Hello new World</p>", $response->data->updateNews->content);
        $this->assertNull($response->data->updateNews->category);
    }

    public function testAPICRUDActionsWithCookieAuthentication() {

        // The api can also be accessed via the main firewall, with uses cookie authentication. If this is the case,
        // we also need to provide a CSRF token with the request.

        // Try READ without csrf token
        $this->assertApiResponse([
            'data' => [
                'findNews' => [
                    'page' => 1
                ]
            ]
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query {
                findNews {
                    page
                }
            }', [], false, 'main')
        );

        // Try READ with csrf token
        $this->assertApiResponse([
            'data' => [
                'findNews' => [
                    'page' => 1
                ]
            ]
        ], $this->api(
            $this->domains['marketing'],
            $this->users['marketing_viewer'],'query {
                findNews {
                    page
                }
            }', [], true, 'main')
        );

        // Try Create without csrf token
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation {
                createNews(data: { title_title: "First News" }, persist: true) {
                    id, 
                    title_title
                }
            }', [], false, 'main');

        $this->assertNotEmpty($response->errors);
        $this->assertStringStartsWith('ERROR: The CSRF token is invalid. Please try to resubmit the form.', $response->errors[0]->message);

        // Try Create with csrf token
        $response = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation {
                createNews(data: { title_title: "First News" }, persist: true) {
                    id, 
                    title_title
                }
            }', [], true, 'main');

        $this->assertNotNull($response->data->createNews->id);
        $this->assertEquals('First News', $response->data->createNews->title_title);

        // Try Update
        $responseUpdate = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($id: ID!) {
                updateNews(id: $id, data: { title_title: "Updated News" }, persist: true) { 
                    title_title
                }
            }', [
                'id' => $response->data->createNews->id,
        ], false, 'main');

        $this->assertNotEmpty($responseUpdate->errors);
        $this->assertStringStartsWith("ERROR: The CSRF token is invalid. Please try to resubmit the form.", $responseUpdate->errors[0]->message);

        $responseUpdate = $this->api(
            $this->domains['marketing'],
            $this->users['marketing_editor'], 'mutation($id: ID!) {
                updateNews(id: $id, data: { title_title: "Updated News" }, persist: true) { 
                    title_title
                }
            }', [
            'id' => $response->data->createNews->id,
        ], true, 'main');

        $this->assertEquals('Updated News', $responseUpdate->data->updateNews->title_title);

    }
}
