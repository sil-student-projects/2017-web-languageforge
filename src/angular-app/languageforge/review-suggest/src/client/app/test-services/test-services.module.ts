import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TestServicesComponent } from './test-services.component';
import { TestServicesRoutingModule } from './test-services-routing.module';
import { ProjectService } from '../shared/services/project.service';

@NgModule({
  imports: [CommonModule, TestServicesRoutingModule],
  declarations: [TestServicesComponent],
  exports: [TestServicesComponent],
  providers: [ProjectService]
})
export class TestServicesModule { }