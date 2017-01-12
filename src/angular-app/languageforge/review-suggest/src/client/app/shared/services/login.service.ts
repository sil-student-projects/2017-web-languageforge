import { Injectable } from '@angular/core';
import { Http, Response, RequestOptions, Headers, RequestMethod } from '@angular/http';
import { Observable } from 'rxjs/Observable';
// import 'rxjs/add/operator/do';  // for debugging

@Injectable()
export class LoginService {

  /**
   * Creates a new ProjectService with the injected Http.
   * @param {Http} http - The injected Http.
   * @constructor
   */
  constructor(private http: Http) { }

  login(username: string, password: string, email: string) {
    let loginUrl = 'http://m.languageforge.local/api/sf';

    let body = {
      'version': '2.0',
      'method': 'user_activate',
      'params': [username, password, email],
      '_remember_me': 'on',
      'id': 1
    };
    let headers = new Headers({ 'Content-Type': 'application/json' });
    let options = new RequestOptions({ headers: headers });

    console.log(JSON.stringify(body));
    return this.http.post(loginUrl, JSON.stringify(body), options)
      .catch(this.handleError);
  }

  readProfile() {
    let loginUrl = 'http://m.languageforge.local/api/sf';

    let body = {
      'version': '2.0',
      'method': 'user_readProfile',
      'id': 1
    };
    let headers = new Headers({ 'Content-Type': 'application/json' });
    let options = new RequestOptions({ headers: headers });

    console.log(JSON.stringify(body));
    return this.http.post(loginUrl, JSON.stringify(body), options)
      .catch(this.handleError);
  }

  /**
    * Handle HTTP error
    */
  private handleError(error: any) {
    // In a real world app, we might use a remote logging infrastructure
    // We'd also dig deeper into the error to get a better message
    let errMsg = (error.message) ? error.message :
      error.status ? `${error.status} - ${error.statusText}` : 'Server error';
    console.error(errMsg); // log to console instead
    return Observable.throw(errMsg);
  }
}