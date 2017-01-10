import { Injectable } from '@angular/core';

import { Response, Headers, Http, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/toPromise';

@Injectable()
export class LfApiService {
    loginUrl = 'http://languageforge.local/app/login_check';
    baseApiUrl = 'http://languageforge.local/api/sf';
    phpSessionCookie: string;

    constructor(private http: Http) {
    }

    private extractData(res: Response) {
        let body = res.json();
        return body.data || { };
    }

    private isAuthenticated(): boolean {
        return false; // not implemented
    }

    performAuthentication() {
        let headers = new Headers({ 'Content-Type': 'application/json' });
        let options = new RequestOptions({ headers: headers });
        return this.http.post(this.loginUrl, {_username: 'admin', _password: 'password', submit: '' }, options).toPromise()
        .then(response => console.log(response.headers));
    }
}
