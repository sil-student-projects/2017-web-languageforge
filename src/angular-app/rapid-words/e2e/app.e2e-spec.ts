import { RapidWordsPage } from './app.po';

describe('rapid-words App', function() {
  let page: RapidWordsPage;

  beforeEach(() => {
    page = new RapidWordsPage();
  });

  it('should display message saying app works', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('app works!');
  });
});
