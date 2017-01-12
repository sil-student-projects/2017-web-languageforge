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
        this.loginService.login("admin", "password", "admin@admin.com")
            .subscribe(
            loginInfo => {
                this.loginInfo = loginInfo;
                console.log(this.loginInfo);
                this.loginService.readProfile()
                    .subscribe(
                        userId => console.log(userId),
                        error => this.errorMessage = <any>error
                    );
            },
            error => this.errorMessage = <any>error
            );
    }
}
