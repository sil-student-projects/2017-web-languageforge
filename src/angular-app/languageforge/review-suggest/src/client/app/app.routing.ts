import { Routes, RouterModule } from '@angular/router'
import { AppComponent } from './app.component'
import { LoginComponent } from './login/login.component'
import { HomeComponent } from './home/home.component'
const appRoutes: Routes = [
    { path: 'login', component: LoginComponent },
    { path: 'app', component: AppComponent},
    { path: 'home', component: HomeComponent }
];
export const appRoutingProviders: any[] = [];

export const routing = RouterModule.forRoot(appRoutes);