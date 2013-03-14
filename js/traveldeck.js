
Ext.application({
    name: 'TravelDeck',
    appFolder: 'js',
    launch: function() {
        Ext.create('Ext.container.Viewport', {
            layout: 'fit',
            items: [
                {
                    title: 'Travel Deck',
                    html : 'Hello! Welcome to Ext JS.'
                }
            ]
        });
    }
});

