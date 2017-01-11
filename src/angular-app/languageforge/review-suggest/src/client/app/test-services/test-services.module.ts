import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TestServicesComponent } from './test-services.component';
import { TestServicesRoutingModule } from './test-services-routing.module';
import { LoginService } from '../shared/services/login.service';

@NgModule({
  imports: [CommonModule, TestServicesRoutingModule],
  declarations: [TestServicesComponent],
  exports: [TestServicesComponent],
  providers: [LoginService]
})
export class TestServicesModule { }