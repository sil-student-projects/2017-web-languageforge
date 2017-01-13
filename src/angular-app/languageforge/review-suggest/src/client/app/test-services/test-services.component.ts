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
    profile: any[] = [];

    ngOnInit() {
        this.loginAsAdmin();
    }

    loginAsAdmin() {
        this.loginService.login("admin", "password", "admin@admin.com")
            .subscribe(
            loginInfo => {
                this.loginInfo = loginInfo;
                console.log(this.loginInfo);
            },
            error => this.errorMessage = <any>error
            );
    }

    readProfile(){
        this.loginService.readProfile()
            .subscribe(
                profile => {
                    this.profile = profile;
                    console.log(this.profile);
                },
                error => this.errorMessage = <any>error 
            );
    }
}
