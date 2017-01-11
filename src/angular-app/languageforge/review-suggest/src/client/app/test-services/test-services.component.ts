import { Component, OnInit } from '@angular/core';
import { LoginService } from '../shared/services/login.service';

@Component({
    moduleId: module.id,
    selector: 'sd-test-services',
    templateUrl: 'test-services.component.html'
})
export class TestServicesComponent implements OnInit {

    constructor(public loginService: LoginService) { }

    errorMessage: string;
    loginInfo: any[] = [];

    ngOnInit() {
        this.loginAsAdmin();
    }

    loginAsAdmin() {
        this.loginService.login("admin", "password")
            .subscribe(
            loginInfo => console.log(this.loginInfo = loginInfo),
            error => this.errorMessage = <any>error
            );
    }
}
