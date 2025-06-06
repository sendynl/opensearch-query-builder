# Build and execute OpenSearch queries using a fluent PHP API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sendynl/opensearch-query-builder.svg?style=flat-square)](https://packagist.org/packages/sendynl/opensearch-query-builder)
[![Tests](https://github.com/sendynl/opensearch-query-builder/actions/workflows/run-tests.yml/badge.svg)](https://github.com/sendynl/opensearch-query-builder/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/sendynl/opensearch-query-builder.svg?style=flat-square)](https://packagist.org/packages/sendynl/opensearch-query-builder)

---

This package is a _lightweight_ query builder for OpenSearch. It is forked from the [spatie/elasticsearch-query-builder](https://github.com/spatie/elasticsearch-query-builder) library and modified to support OpenSearch. We're always open for PRs if you need anything specific!

```php
use Sendy\OpenSearchQueryBuilder\Aggregations\MaxAggregation;
use Sendy\OpenSearchQueryBuilder\Builder;
use Sendy\OpenSearchQueryBuilder\Queries\MatchQuery;

$transport = (new \OpenSearch\TransportFactory())->create();

$client = new \OpenSearch\Client($transport);

$companies = (new Builder($client))
    ->index('companies')
    ->addQuery(MatchQuery::create('name', 'sendy', fuzziness: 3))
    ->addAggregation(MaxAggregation::create('score'))
    ->search();
```

## Installation

You can install the package via composer:

```bash
composer require sendynl/opensearch-query-builder
```

## Basic usage

The only class you really need to interact with is the `Sendy\OpenSearchQueryBuilder\Builder` class. It requires an `\OpenSearch\Client` passed in the constructor. Take a look at the [OpenSearch SDK docs](https://docs.opensearch.org/docs/latest/clients/php/) to learn more about connecting to your OpenSearch cluster.

The `Builder` class contains some methods to [add queries](#adding-queries), [aggregations](#adding-aggregations), [sorts](#adding-sorts), [fields](#retrieve-specific-fields) and some extras for [pagination](#pagination). You can read more about these methods below. Once you've fully built-up the query you can use `$builder->search()` to execute the query or `$builder->getPayload()` to get the raw payload for OpenSearch.

```php
use Sendy\OpenSearchQueryBuilder\Queries\RangeQuery;
use Sendy\OpenSearchQueryBuilder\Builder;

$transport = (new \OpenSearch\TransportFactory())->create();

$client = new \OpenSearch\Client($transport);

$builder = new Builder($client);

$builder->addQuery(RangeQuery::create('age')->gte(18));

$results = $builder->search(); // raw response from OpenSearch
```

#### Multi-Search Queries

Multi-Search queries are also available using the [`MultiBuilder` class](#multi-search-query-builder).

## Adding queries

The `$builder->addQuery()` method can be used to add any of the available `Query` types to the builder. The available query types can be found below or in the `src/Queries` directory of this repo. Every `Query` has a static `create()` method to pass its most important parameters.

The following query types are available:

#### `ExistsQuery`

https://docs.opensearch.org/docs/latest/query-dsl/term/exists/

```php
\Sendy\OpenSearchQueryBuilder\Queries\ExistsQuery::create('terms_and_conditions');
```

#### `GeoshapeQuery`

https://docs.opensearch.org/docs/latest/query-dsl/geo-and-xy/geoshape/

```php
\Sendy\OpenSearchQueryBuilder\Queries\GeoshapeQuery::create(
  'location',
  \Sendy\OpenSearchQueryBuilder\Queries\GeoshapeQuery::TYPE_POLYGON,
  [[1.0, 2.0]],
  \Sendy\OpenSearchQueryBuilder\Queries\GeoShapeQuery::RELATION_INTERSECTS,
);
```

#### `MatchQuery`

https://docs.opensearch.org/docs/latest/query-dsl/full-text/match/

```php
\Sendy\OpenSearchQueryBuilder\Queries\MatchQuery::create('name', 'john doe', fuzziness: 2, boost: 5.0);
```

#### `MatchPhraseQuery`

https://docs.opensearch.org/docs/latest/query-dsl/full-text/match-phrase/

```php
\Sendy\OpenSearchQueryBuilder\Queries\MatchPhraseQuery::create('name', 'john doe', slop: 2,zeroTermsQuery: "none",analyzer: "my_analyzer");
```

#### `MultiMatchQuery`

https://docs.opensearch.org/docs/latest/query-dsl/full-text/multi-match/

```php
\Sendy\OpenSearchQueryBuilder\Queries\MultiMatchQuery::create('john', ['email', 'email'], fuzziness: 'auto');
```

#### `NestedQuery`

https://docs.opensearch.org/docs/latest/query-dsl/joining/nested/

```php
\Sendy\OpenSearchQueryBuilder\Queries\NestedQuery::create(
    'user',
    new \Sendy\OpenSearchQueryBuilder\Queries\MatchQuery('name', 'john')
);
```

##### `NestedQuery` `InnerHits`

https://docs.opensearch.org/docs/latest/search-plugins/searching-data/inner-hits/

```php
$nestedQuery = \Sendy\OpenSearchQueryBuilder\Queries\NestedQuery::create(
    'comments',
    \Sendy\OpenSearchQueryBuilder\Queries\TermsQuery::create('comments.published', true)
);

$nestedQuery->innerHits(
    \Sendy\OpenSearchQueryBuilder\Queries\NestedQuery\InnerHits::create('top_three_liked_comments')
        ->size(3)
        ->addSort(
            \Sendy\OpenSearchQueryBuilder\Sorts\Sort::create(
                'comments.likes',
                \Sendy\OpenSearchQueryBuilder\Sorts\Sort::DESC
            )
        )
        ->fields(['comments.content', 'comments.author', 'comments.likes'])
);
```

#### `RangeQuery`

https://docs.opensearch.org/docs/latest/query-dsl/term/range/

```php
\Sendy\OpenSearchQueryBuilder\Queries\RangeQuery::create('age')
    ->gte(18)
    ->lte(1337);
```

#### `TermQuery`

https://docs.opensearch.org/docs/latest/query-dsl/term/term/

```php
\Sendy\OpenSearchQueryBuilder\Queries\TermQuery::create('user.id', 'flx');
```

#### `TermsQuery`

https://docs.opensearch.org/docs/latest/query-dsl/term/terms/

```php
\Sendy\OpenSearchQueryBuilder\Queries\TermsQuery::create('user.id', ['flx', 'fly'], boost: 5.0);
```

#### `WildcardQuery`

https://docs.opensearch.org/docs/latest/query-dsl/term/wildcard/

```php
\Sendy\OpenSearchQueryBuilder\Queries\WildcardQuery::create('user.id', '*doe');
```

#### `PercolateQuery`

https://docs.opensearch.org/docs/latest/field-types/supported-field-types/percolator/

```php
\Sendy\OpenSearchQueryBuilder\Queries\PercolateQuery::create('query', ['title' => 'foo', 'body' => 'bar']);
```

#### `BoolQuery`

https://docs.opensearch.org/docs/latest/query-dsl/compound/bool/

```php
\Sendy\OpenSearchQueryBuilder\Queries\BoolQuery::create()
    ->add($matchQuery, 'must_not')
    ->add($existsQuery, 'must_not');
```

#### `collapse`

The `collapse` feature allows grouping search results by a specific field while retrieving top documents from each group using `inner_hits`. This is useful for avoiding duplicate entities in search results while still accessing grouped data.

https://docs.opensearch.org/docs/latest/search-plugins/collapse-search/

```php
use Sendy\OpenSearchQueryBuilder\Sorts\Sort;
use Sendy\OpenSearchQueryBuilder\Builder;

// Initialize ExtendedBuilder with an OpenSearch client
$builder = new Builder($client);

// Apply collapse to group by 'user_id'
$builder->collapse(
    'user_id', // Field to collapse on
    [
        'name' => 'top_three_liked_posts',
        'size' => 3, // Retrieve top 3 posts per user
        'sort' => [
            Sort::create('post.likes', Sort::DESC), // Sort posts by likes (descending)
        ],
        'fields' => ['post.title', 'post.content', 'post.likes'], // Select specific fields
    ],
    10, // Max concurrent group searches
);

// Execute the search
$response = $builder->search();
```

### Chaining multiple queries

Multiple `addQuery()` calls can be chained on one `Builder`. Under the hood they'll be added to a `BoolQuery` with occurrence type `must`. By passing a second argument to the `addQuery()` method you can select a different occurrence type:

```php
$builder
    ->addQuery(
        MatchQuery::create('name', 'billie'),
        'must_not' // available types: must, must_not, should, filter
    )
    ->addQuery(
        MatchQuery::create('team', 'eillish')
    );
```

More information on the boolean query and its occurrence types can be found [in the OpenSearch docs](https://docs.opensearch.org/docs/latest/query-dsl/compound/bool/).

## Adding aggregations

The `$builder->addAggregation()` method can be used to add any of the available `Aggregation`s to the builder. The available aggregation types can be found below or in the `src/Aggregations` directory of this repo. Every `Aggregation` has a static `create()` method to pass its most important parameters and sometimes some extra methods.

```php
use Sendy\OpenSearchQueryBuilder\Aggregations\TermsAggregation;
use Sendy\OpenSearchQueryBuilder\Builder;

$transport = (new \OpenSearch\TransportFactory())->create();

$client = new \OpenSearch\Client($transport);

$results = (new Builder($client))
    ->addAggregation(TermsAggregation::create('genres', 'genre'))
    ->search();

$genres = $results['aggregations']['genres']['buckets'];
```

The following query types are available:

#### `CardinalityAggregation`

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\CardinalityAggregation::create('team_agg', 'team_name');
```

#### `FilterAggregation`

https://docs.opensearch.org/docs/latest/aggregations/bucket/filter/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\FilterAggregation::create(
    'tshirts',
    \Sendy\OpenSearchQueryBuilder\Queries\TermQuery::create('type', 'tshirt'),
    \Sendy\OpenSearchQueryBuilder\Aggregations\MaxAggregation::create('max_price', 'price')
);
```

#### `MaxAggregation`

https://docs.opensearch.org/docs/latest/aggregations/metric/maximum/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\MaxAggregation::create('max_price', 'price');
```

#### `MinAggregation`

https://docs.opensearch.org/docs/latest/aggregations/metric/minimum/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\MinAggregation::create('min_price', 'price');
```

#### `SumAggregation`

https://docs.opensearch.org/docs/latest/aggregations/metric/sum/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\SumAggregation::create('sum_price', 'price');
```

#### `NestedAggregation`

https://docs.opensearch.org/docs/latest/aggregations/bucket/nested/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\NestedAggregation::create(
    'resellers',
    'resellers',
    \Sendy\OpenSearchQueryBuilder\Aggregations\MinAggregation::create('min_price', 'resellers.price'),
    \Sendy\OpenSearchQueryBuilder\Aggregations\MaxAggregation::create('max_price', 'resellers.price'),
);
```

#### `ReverseNestedAggregation`

https://docs.opensearch.org/docs/latest/aggregations/bucket/reverse-nested/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\ReverseNestedAggregation::create(
    'name',
    ...$aggregations
);
```

#### `TermsAggregation`

https://docs.opensearch.org/docs/latest/aggregations/bucket/terms/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\TermsAggregation::create(
    'genres',
    'genre'
)
    ->size(10)
    ->order(['_count' => 'asc'])
    ->missing('N/A')
    ->aggregation(/* $subAggregation */);
```

#### `TopHitsAggregation`

https://docs.opensearch.org/docs/latest/aggregations/metric/top-hits/

```php
\Sendy\OpenSearchQueryBuilder\Aggregations\TopHitsAggregation::create(
    'top_sales_hits',
    size: 10,
);
```

## Adding sorts

The `Builder` (and some aggregations) has a `addSort()` method that takes a `Sort` instance to sort the results. You can read more about how sorting works in [the OpenSearch docs](https://docs.opensearch.org/docs/latest/search-plugins/searching-data/sort/).

```php
use Sendy\OpenSearchQueryBuilder\Sorts\Sort;

$builder
    ->addSort(Sort::create('age', Sort::DESC))
    ->addSort(
        Sort::create('score', Sort::ASC)
            ->unmappedType('long')
            ->missing(0)
    );
```

### Nested sort

https://docs.opensearch.org/docs/latest/search-plugins/searching-data/sort/#sorting-nested-objects

```php
use Sendy\OpenSearchQueryBuilder\Sorts\NestedSort;

$builder
    ->addSort(
        NestedSort::create('books', 'books.rating', NestedSort::ASC)
    );
```

#### Nested sort with filter

```php
use Sendy\OpenSearchQueryBuilder\Sorts\NestedSort;
use Sendy\OpenSearchQueryBuilder\Queries\BoolQuery;
use Sendy\OpenSearchQueryBuilder\Queries\TermQuery;

$builder
    ->addSort(
        NestedSort::create(
            'books',
            'books.rating',
            NestedSort::ASC
        )->filter(BoolQuery::create()->add(TermQuery::create('books.category', 'comedy'))
    );
```

## Retrieve specific fields

The `fields()` method can be used to request specific fields from the resulting documents without returning the entire `_source` entry. You can read more about the specifics of the fields parameter in [the OpenSearch docs](https://docs.opensearch.org/docs/latest/search-plugins/searching-data/retrieve-specific-fields/).

```php
$builder->fields('user.id', 'http.*.status');
```

## Highlighting

The `highlight()` method can be used to add a highlight section to your query along the rules in [the OpenSearch docs](https://docs.opensearch.org/docs/latest/search-plugins/searching-data/highlight/).

```php
$highlightSettings = [
    'pre_tags' => ['<em>'],
    'post_tags' => ['</em>'],
    'fields' => [
        '*' => (object) []
    ]
];

$builder->highlight($highlightSettings);
```

## Post filter

The `addPostFilterQuery()` method can be used to add a post_filter BoolQuery to your query along the rules in [the OpenSearch docs](https://docs.opensearch.org/docs/latest/search-plugins/filter-search/#narrowing-results-using-post-filter-while-preserving-aggregation-visibility).

```php
use Sendy\OpenSearchQueryBuilder\Queries\TermsQuery;

$builder->addPostFilterQuery(TermsQuery::create('user.id', ['flx', 'fly']));
```

## Pagination

Finally the `Builder` also features a `size()` and `from()` method for the corresponding OpenSearch search parameters. These can be used to build a paginated search. Take a look the following example to get a rough idea:

```php
use Sendy\OpenSearchQueryBuilder\Builder;

$pageSize = 100;
$pageNumber = $_GET['page'] ?? 1;

$transport = (new \OpenSearch\TransportFactory())->create();

$client = new \OpenSearch\Client($transport);

$pageResults = (new Builder($client))
    ->size($pageSize)
    ->from(($pageNumber - 1) * $pageSize)
    ->search();
```

## Multi-Search Query Builder

OpenSearch provides a ["multi-search" API](https://docs.opensearch.org/docs/latest/api-reference/search-apis/multi-search/) that allows for multiple query bodies to be included in a single request.

Use the `MultiBuilder` class and [add builders](#add-builders) to add builders to your query request. The response will include a `responses` array of the query results, in the same order the requests are added. Use the `$multiBuilder->search()` to execute the queries, or `$multiBuilder->getPayload()` for the raw request payload.

```php
use Sendy\OpenSearchQueryBuilder\MultiBuilder;
use Sendy\OpenSearchQueryBuilder\Builder;

$transport = (new \OpenSearch\TransportFactory())->create();

$client = new \OpenSearch\Client($transport);

$multiBuilder = (new MultiBuilder($client));

$multiBuilder->addBuilder(
    (new Builder($client))->index('custom_index')->size(10)
);
// you can pass the index name to the addBuilder method second param
$multiBuilder->addBuilder(
    (new Builder($client))->size(10)
    'different_index'
);

$multiResults = $multiBuilder->search();
```

Returns the following response JSON shape:

```
{
    "took": 2,
    "responses": [
        {... first query result ...},
        {... second query result ...},
    ]
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you've found a bug regarding security please mail security@sendy.nl instead of using the issue tracker.

## Credits

-   [Alex Vanderbist](https://github.com/alexvanderbist)
-   [Ruben Van Assche](https://github.com/rubenvanassche)
-   [Sendy](https://github.com/sendynl)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
