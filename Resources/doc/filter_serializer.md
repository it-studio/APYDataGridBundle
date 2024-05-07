Grid filter serializer
========

Can be used for serializing/unserializing the state of a grid (state of its filters, order and current page).
There is an **implementation of URL filter serializer** for a grid state, which can be used like this:

```php
    /**
      * this is a grid controller action
      */
    public function grid(Request $request)
    {
        // create a grid connected to a source
        $source = $this->get("apy_grid.source_factory")->create("entity", ["entity" => MyEntity::class]);
        $grid = $this->createGrid("my_entity", $source);

        // get an URL filter serializer
        $urlFilterSerializer = $this->get("apy.grid.url_filter_serializer");

        // we can init the grid from serialized grid state (?f=filter_state...)
        $state = $request->get("f");
        if (!empty($state)) {
            $urlFilterSerializer->unserialize($state);
            // set the default grid state accordingly to the serialized state
            $urlFilterSerializer->setToGrid($grid);
        }

        $response = $grid->getGridResponse('MyEntity/grid.html.twig');

        // serialize current filter state (set from request)
        $urlFilterSerializer->setFromGrid($grid);
        $urlFilter = $urlFilterSerializer->serialize();
        // for any use
        var_dump($urlFilter);

        return $response;
    }
```

Any kind of a serializer can be implemented with the help of a ``AbstractFilterSerializer`` class.
